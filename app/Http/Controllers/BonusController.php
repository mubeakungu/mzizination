<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\BonusLevel;
use App\Post;
use App\User;
use App\Transaction;
use DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BonusController extends Controller
{
    public function init()
    {
        return [
            'repostInfo' => [
                'total' => $this->user->total_reposts,
                'levels' => BonusLevel::orderBy('goal', 'asc')->get(),
                'bonusBalance' => $this->user->bonus_balance
            ],
            'bonuses' => [
                'daily' => $this->user->bonus_daily,
                'one' => $this->user->bonus_use ? 86400 * 999999 : 0
            ],
            'onetime' => $this->config->onetime_bonus
        ];
    }

    public function take(Request $request)
    {
        if(!in_array($request->type, ['daily', 'one'])) return;

        if(!$this->isGroupMember()) {
            return [
                'error' => true,
                'message' => 'Подпишитесь на группу VK'
            ];
        }

        if(!$this->user->tg_id) {
            return [
                'error' => true,
                'message' => 'Подпишитесь на все группы Telegram',
                'showModal' => true
            ];
        }

        if(!$this->isChannelMember()) {
            return [
                'error' => true,
                'message' => 'Подпишитесь на все группы Telegram'
            ];
        }

        if($this->user->balance >= 10) {
            return [
                'error' => true,
                'message' => 'Нельзя получить бонус, если ваш баланс более 10!'
            ];
        }

        if(\App\Mine::where('status', 0)->where('user_id', $this->user->id)->first()) {
            return [
                'error' => true,
                'message' => 'Завершите игру в Mines'
            ];
        }

        try {
            DB::beginTransaction();

            $user = User::lockForUpdate()->where('id', $this->user->id)->first();
            $remaining = null;
            switch($request->type) {
                case 'daily':
                    if($user->bonus_daily > time()) {
                        DB::rollback();
                        return [
                            'error' => true,
                            'message' => 'Бонус можно получить через ' . $this->remainingTime($user->bonus_daily - time())
                        ];
                    }
                    
                    $bonus = rand(
                        $this->rankService->get()->min_bonus * 100, 
                        $this->rankService->get()->max_bonus * 100
                    ) / 100;

                    $user->balance += $bonus;
                    $user->bonus_daily = time() + 86400;
                    $user->save();

                    $remaining = $user->bonus_daily;

                    Transaction::create([
                        'user_id' => $this->user->id,
                        'action'  => 'Получен ежедневный бонус',
                        'amount'  => $bonus,
                        'type'    => 'up'
                    ]);
                break;
                
                case 'one':
                    if($user->bonus_use) {
                        DB::rollback();
                        return [
                            'error' => true,
                            'message' => 'Вы уже получали бонус'
                        ];
                    }

                    $bonus = $this->config->onetime_bonus;

                    $user->balance += $bonus;
                    $user->bonus_use = 1;
                    $user->save();

                    Transaction::create([
                        'user_id' => $this->user->id,
                        'action'  => 'Получен одноразовый бонус',
                        'amount'  => $bonus,
                        'type'    => 'up'
                    ]);
                break;
            }

            if(!$user->referral_send && $user->referral_use) {
                $refer = User::find($user->referral_use);
                $refer->increment('referral_balance', $this->config->referral_reward);

                $user->referral_send = 1;
                $user->save();

                \App\ReferralProfit::create([
                    'from_id' => $user->id,
                    'ref_id' => $refer->id,
                    'amount' => $this->config->referral_reward,
                    'level' => 1
                ]);
            }
            
            DB::commit();
        } catch(\Exception $e) {
            DB::rollback();
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        return [
            'balance' => $user->balance,
            'text' => 'Вы получили <b>' . $bonus . '</b>',
            'type' => $request->type,
            'remaining' => $remaining
        ];
    }

    public function transfer()
    {
        try {
            DB::beginTransaction();

            $user = User::lockForUpdate()->where('id', $this->user->id)->first();

            if($user->bonus_balance < 5) {
                DB::rollback();
                return [
                    'error' => true,
                    'message' => 'Минимум 5'
                ];
            }

            $user->balance += $user->bonus_balance;
            $user->bonus_balance = 0;
            $user->save();
            
            DB::commit();
        } catch(\Exception $e) {
            DB::rollback();
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        return [
            'balance' => $user->balance
        ];
    }

    public function checkReposts()
    {
        if(!$this->isGroupMember()) {
            return [
                'error' => true,
                'message' => 'Подпишитесь на группу VK'
            ];
        }
        
        try {
            DB::beginTransaction();

            $user = User::lockForUpdate()->where('id', $this->user->id)->first();
            $level = $this->getLevel();
            $newRepost = $this->countPosts(); 
    
            if(count($newRepost) == 0) {
                return [
                    'error' => true,
                    'message' => 'Новых действий не обнаружено'
                ];
            }

            foreach($newRepost as $id) {
                Post::create(['owner_id' => $this->user->id, 'post_id' => $id]);
            }
    
            $user->increment('bonus_balance', count($newRepost) * $level->reward);
            $user->increment('total_reposts', count($newRepost));

            DB::commit();
        } catch(\Exception $e) {
            DB::rollback();
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        return [
            'total' => $user->total_reposts,
            'bonusBalance' => $user->bonus_balance
        ];
    }

    private function getLevel()
    {
        $levels = BonusLevel::orderBy('goal', 'asc')->get();

        if(!count($levels)) {
            throw new HttpException(500, 'Администратор не добавил ни одного уровня');
        }

        $bonusId = $levels[0]->id;
        $bonusMax = $levels[count($levels) - 1];

        foreach($levels as $index => $level) {
            if(
                $levels[$index === 0 ? 0 : $index - 1]->goal <= $this->user->total_reposts &&
                $level->goal > $this->user->total_reposts
            ) $bonusId = $level->id;
        }

        if($this->user->total_reposts >= $bonusMax->goal) {
            $bonusId = $bonusMax->id;
        }

        return BonusLevel::find($bonusId);
    }
    
    private function getPosts($owner_id)
    {
        $data = [
            'owner_id'     => $owner_id,
            'access_token' => $this->config->vk_service_token,
            'count'        => 15,
            'v'            => '5.131'
        ];

        $posts = $this->curl('https://api.vk.com/method/wall.get?' . http_build_query($data));
        
        if(isset($posts['error'])) {
            throw new HttpException(500, $posts['error']['error_msg']);
        }

        return $posts['response']['items'];
    }

    private function countPosts()
    {
        $userPosts = $this->getPosts($this->user->vk_id);
        $groupPosts = $this->getPosts(-$this->config->vk_id);

        $userID = [];
        $groupID = [];

        $newID = [];

        foreach($userPosts as $userPost) { // получаем id репостнутой записи
            @$userID[] = $userPost['copy_history'][0]['id'];
        }

        foreach($groupPosts as $groupPost) { // получаем id записи в сообществе
            @$groupID[] = $groupPost['id'];
        }

        foreach($userID as $id) {
            if(in_array($id, $newID)) continue;
            if(!in_array($id, $groupID)) continue;
            if(Post::where('owner_id', $this->user->id)->where('post_id', $id)->count() !== 0) continue;

            $newID[] = $id;
        }

        return $newID;
    }
}