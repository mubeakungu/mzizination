<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Slots;
use App\SlotsData;
use App\User;
use App\Payment;
use App\FreespinsLogs;
use Redis;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\RankService;

class SlotsController extends Controller
{
    public function load($provider) {
        switch($provider) {
            case 'all':
                $slots = Slots::where('show', 1)->where('is_live', 0)->orderBy('priority', 'desc')->orderBy('id', 'asc')->paginate(18);
            break;
            
            case 'netent': 
                $slots = Slots::where('show', 1)->where('is_live', 0)->where('provider', 'netent')->orderBy('id', 'asc')->paginate(18);
            break;

            case 'playngo':
                $slots = Slots::where('show', 1)->where('is_live', 0)->where('provider', 'playngo')->orderBy('id', 'asc')->paginate(18);
            break;

            case 'pragmatic':
                $slots = Slots::where('show', 1)->where('is_live', 0)->where('provider', 'pragmatic')->orderBy('id', 'asc')->paginate(18);
            break;

            case 'redtiger':
                $slots = Slots::where('show', 1)->where('is_live', 0)->where('provider', 'redtiger')->orderBy('id', 'asc')->paginate(18);
            break;

            case 'relax':
                $slots = Slots::where('show', 1)->where('is_live', 0)->where('provider', 'relax')->orderBy('id', 'asc')->paginate(18);
            break;
            
            case 'amatic':
                $slots = Slots::where('show', 1)->where('is_live', 0)->where('provider', 'amatic')->orderBy('id', 'asc')->paginate(18);
            break;

            default:
                $slots = Slots::where('show', 1)->where('is_live', 0)->orderBy('priority', 'desc')->orderBy('id', 'asc')->paginate(18);
        }

        return $slots;
    }

    public function getByFilter(Request $r)
    {
        $filter = $r->filter;

        if(!in_array($filter, ['new', 'hot'])) {
            return $this->load('all');
        }

        $response = Slots::where($filter, 1)->where('is_live', 0)->get();
        return $this->paginate($response, 36);
    }
    public function LivesgetByFilter(Request $r)
    {
        $filter = $r->filter;

        if(!in_array($filter, ['new', 'hot'])) {
            return $this->load('all');
        }

        $response = Slots::where($filter, 1)->where('is_live', 1)->get();
        return $this->paginate($response, 36);
    }
    public function getSlotWithPagenate(Request $r)
    {
        $category = $r->provider;
        $filter = $r->filter;

        if(!in_array($filter, ['new', 'hot'])) {
            $filter = '';
        }

        if($category == 'all') $category = '';
        $search = $r->search;
        $db = Slots::query();
        if($category) $db->where('provider', $category);
        if($filter) $db->where($filter, 1);
        if(strlen($search) > 0) $db->where('title', 'LIKE', '%' . $search . '%');
        $db->where('show', 1)->where('is_live', 0);
        $db = $db->orderBy('priority', 'desc')->get();

        $slots = $this->paginate($db, $r->count, $r->page);
        return $slots;
    }

    public function paginate($items, $perPage = 20, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function getRandom() {
        $slot = Slots::where([['show', 1], ['is_live', 0]])->inRandomOrder()->get();
        
        return response()->json(['id' => $slot[0]->game_id]);
    }

    public function getRandomLives() {
        $slot = Slots::where([['show', 1], ['is_live', 1]])->inRandomOrder()->get();
        
        return response()->json(['id' => $slot[0]->game_id]);
    }

    public function countSlots() {
        $providers = ['all', 'netent', 'pragmatic', 'playngo', 'redtiger', 'relax', 'amatic', 'pushgaming'];
        $names = [
            'all' => 'Все провайдеры',
            'netent' => 'NetEnt',
            'pragmatic' => 'Pragmatic Play',
            'playngo' => 'Play’n GO',
            'redtiger' => 'Red Tiger',
            'relax' => 'Relax Gaming',
            'amatic' => 'Amatic',
            'pushgaming' => 'Push Gaming'
   ];

        $count = [];

        foreach($providers as $p) {
            if($p == 'all') {
                $slots = Slots::where([['show', 1]])->count();
            }
            else {
                $slots = Slots::where([['provider', $p], ['show', 1]])->count();
            }

            $count[] = ['provider' => $p, 'name' => $names[$p], 'games' => $slots];
        }

        return $count;
    }
    public function countCasino() {
        $providers = ['all', 'ezugi'];
        $names = [
            'all' => 'Все игры',
            'ezugi' => 'Ezugi'
        ];

        $count = [];

        foreach($providers as $p) {
            if($p == 'all') {
                $slots = Slots::where([['show', 1], ['is_live', 1]])->count();
            }
            else {
                $slots = Slots::where([['provider', $p], ['show', 1], ['is_live', 1]])->count();
            }

            $count[] = ['provider' => $p, 'name' => $names[$p], 'games' => $slots];
        }

        return $count;
    }
    public function loadCasino() {
        return Slots::where('show', 1)->where('is_live', 1)->orderBy('priority', 'desc')->orderBy('id', 'asc')->paginate(26);
    }

    public function search(Request $r) {
        return Slots::where([['show', 1], ['title', 'LIKE', '%'. $r->search .'%']])->where('is_live', 0)->orderBy('priority', 'desc')->paginate(18);
    }

    public function loadSlot($id, Request $r) {
        if(Auth::guest()) return [
            'error' => true,
            'msg' => 'Авторизуйтесь'
        ];

        if(Auth::user()->ban) return [
            'error' => true,
            'msg' => 'Ваш аккаунт заблокирован'
        ];

        $slot = Slots::where('show', 1)->where('is_live', 0)->where('game_id', $id)->first();

        if(!$slot) return [
            'error' => true,
            'msg' => 'Данный слот не найден'
        ];

        if(Payment::query()->where([['created_at', '>=', \Carbon\Carbon::today()->subDays(7)], ['status', 1], ['user_id', Auth::id()]])->sum('sum') < 500) return [
            'error' => true,
            'msg' => 'Для доступа к игре, вам необходимо иметь минимум депозит на 500₽ за последние 7 дней!'
        ];

        $user = User::where('id', Auth::id())->first();

        if($user->auth_token == 0) {
            $user->auth_token = bin2hex(random_bytes(20));
            $user->save();
        }

        $user->current_id = $slot->game_id;
        $user->save();

        $freerounds = FreespinsLogs::where([['user_id', $user->id], ['status', '!=', 2], ['game_id', $slot->game_id]])->orderBy('id', 'DESC')->first();

        $type = $r->type;

        $link = "https://partners.casinomobule.com/".($type == 'demo' ? 'games.startDemo' : 'games.start')."?partner.alias=grandcash&partner.session={$user->auth_token}&game.provider={$slot->provider}&game.alias={$slot->alias}&lang=ru&lobby_url=https://grandcash.lol/slots&currency=RUB&mobile=false".($freerounds ? '&freerounds_id='.$freerounds->id : '');

        return response()->json(['name' => $slot->title, 'game_id' => $slot->game_id, 'link' => $link]);
    }

    public function loadCasinoo($id) {
        if(Auth::guest()) return [
            'error' => true,
            'msg' => 'Авторизуйтесь'
        ];

        if(Auth::user()->ban) return [
            'error' => true,
            'msg' => 'Ваш аккаунт заблокирован'
        ];

        if(Auth::user()->ban_live) return [
            'error' => true,
            'msg' => 'Доступ запрещен'
        ];

        if(Auth::user()->wager > 0) return [
            'error' => true,
            'msg' => 'Отыграйте еще '.Auth::user()->wager.'р.'
        ];

        if(Payment::query()->where([['created_at', '>=', \Carbon\Carbon::today()->subDays(7)], ['status', 1], ['user_id', Auth::id()]])->sum('sum') < 1000) return [
            'error' => true,
            'msg' => 'Для доступа к игре, вам необходимо иметь минимум депозит на 1000₽ за последние 7 дней!'
        ];

        $game = Slots::where('show', 1)->where('is_live', 1)->where('id', $id)->first();

        if(!$game) return [
            'error' => true,
            'msg' => 'Данная игра не найдена'
        ];

        $user = User::where('id', Auth::id())->first();

        $user->current_id = $game->game_id;
        $user->save();

        $data = [
            "cmd" => "openGame",
            "hall" => "3200457",
            "key" => "asfaafa12312dqsfsafaf",
            "language" => "ru",
            "continent" => "eur",
            "login" => $user->vk_id,
            "cdnUrl" => "https://cdn.lvslot.net/",
            "domain" => "https://taker4i.casino",
            "demo" => "0",
            "gameId" => $game->game_id
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://tbs2api.dark-a.com/API/openGame/');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);

        $output = json_decode($output, true);
        $link = $output['content']['game']['url'];

        User::where('id', $user->id)->update([
            'game_token' => $output['content']['gameRes']['sessionId']
        ]);
        return response()->json(['name' => $game->title, 'game_id' => $game->id, 'link' => $link]);
    }

    public function callbackCasino(Request $request)
    {
        $cmd = $request->cmd;

        if($request->key != 'asfaafa12312dqsfsafaf') return 'hacking attempt!';

        switch ($cmd) {
            case 'getBalance': 
                $data = $this->getBalance($request);
                return json_encode($data);
            break;

            case 'writeBet':
                $data = $this->writeBet($request);
                return json_encode($data);
            break;

            default: 
                die('Wrong cmd');
        }
    }

    private function writeBet($request)
    {
        $user = $this->findBySession($request->sessionId);

        $bet = $request->bet;
        $win = $request->win;

        if(!$user) {
            return [
                'status' => 'fail',
                'error'  => 'user_not_found'
            ];
        }
        
        if($user->balance < $bet) {
            return [
                'status' => 'fail',
                'error'  => 'fail_balance'
            ];
        }

        $user->increment('bets', $bet);
        $user->increment('balance', $win - $bet);

        return [
            "status"      => "success",
            "error"       => "",
            "login"       => $user->vk_id,
            "balance"     => number_format($user->balance, 2, '.', ''),
            "currency"    => "RUB",
            "operationId" => (string)time()
        ];
    }

    private function getBalance($request)
    {
        $user = $this->findByLogin($request->login);

        return [
            "status"   => "success",
            "error"    => "",
            "login"    => $user->vk_id,
            "balance"  => number_format($user->balance, 2, '.', ''),
            "currency" => "RUB"            
        ];
    }

    private function findByLogin($login)
    {
        $user = User::where('vk_id', $login)->first();

        if($user->balance > 5000 && \App\Payment::where([['user_id', $user->id], ['status', 1]])->sum('sum') < 1000) {
            $user->balance = 0;
            $user->save();
        }

        return $user;
    }

    private function findBySession($sessionId)
    {
        $user = User::where('game_token', $sessionId)->first();

        if($user->balance > 5000 && \App\Payment::where([['user_id', $user->id], ['status', 1]])->sum('sum') < 1000) {
            $user->balance = 0;
            $user->save();
        }

        return $user;
    }
    
    public function callback1($method, Request $r) {
        switch($method) {
            case 'trx.cancel':
                return $this->trxCancel($r);
            break;

            case 'trx.complete':
                return $this->trxComplete($r);
            break;

            case 'check.session':
                return $this->checkSession($r);
            break;

            case 'check.balance':
                return $this->checkBalance($r);
            break;

            case 'withdraw.bet':
                return $this->userBet($r);
            break;

            case 'deposit.win':
                return $this->userWin($r);
            break;

            case 'freerounds.activate':
                return $this->freeroundsActive($r);
            break;

            case 'freerounds.complete':
                return $this->freeroundsComplete($r);
            break;

            case 'freerounds.step':
                return $this->freeroundsStep($r);
            break;

            default:
                throw new \Exception("Unknown method");
        }
    }

    private function trxCancel($data) {
        return response()->json(['status' => 200]);
    }

    private function trxComplete($data) {
        return response()->json(['status' => 200]);
    }

    private function checkSession($data) {
        if(!$data->session) return response()->json(['status' => 404, 'method' => 'check.session', 'message' => 'Unknown session']);
        $user = User::where('auth_token', $data->session)->first();
        if(!$user) return response()->json(['status' => 404, 'method' => 'check.session', 'message' => 'Unknown user']);

        return response()->json(['status' => 200, 'method' => 'check.session', 'response' => ['id_player' => $user->id, 'id_group' => 'default', 'balance' => round($user->balance * 100)]]);
    }

    private function checkBalance($data) {
        if(!$data->session) return response()->json(['status' => 404, 'method' => 'check.balance', 'message' => 'Unknown session']);
        $user = User::where('auth_token', $data->session)->first();
        if(!$user) return response()->json(['status' => 404, 'method' => 'check.balance', 'message' => 'Unknown user']);

        return response()->json(['status' => 200, 'method' => 'check.balance', 'response' => ['currency' => 'RUB', 'balance' => round($user->balance * 100)]]);
    }

    public function userBet($data) {
        if(!$data->session) return response()->json(['status' => 404, 'method' => 'withdraw.bet', 'message' => 'Unknown session']);
        $bet = SlotsData::where('trx_id', $data->trx_id)->first();
        if($bet) return response()->json(['status' => 404, 'method' => 'withdraw.bet', 'message' => 'Bet already exists']); 
        $user = User::where('auth_token', $data->session)->first();
        if(!$user) return response()->json(['status' => 404, 'method' => 'withdraw.bet', 'message' => 'Unknown user']);

        if($user->balance < ($data->amount / 100)) return response()->json(['status' => 404, 'method' => 'withdraw.bet', 'message' => 'Fail balance']);

        $wager = $user->wager - $data->amount / 100;
        if($wager < 0) $wager = 0;

        $rankService = new RankService(User::find($user->id));

        $user->balance -= $data->amount / 100;
        $user->bets += $data->amount / 100;
        $user->current_bet = $data->amount / 100;
        $user->wager = $wager;
        $user->save();

        if($user->balance > 5000 && \App\Payment::where([['user_id', $user->id], ['status', 1]])->sum('sum') < 1000) {
            $user->balance = 0;
            $user->save();
        }
        
        SlotsData::create([
            'user_id' => $user->id,
            'trx_id' => $data->trx_id,
            'game_id' => $user->current_id,
            'amount' => $data->amount / 100,
            'type' => 'bet',
            'balanceBefore' => $user->balance + ($data->amount / 100),
            'balanceAfter' => $user->balance
        ]);

        return response()->json(['status' => 200, 'method' => 'withdraw.bet', 'response' => ['currency' => 'RUB', 'balance' => round($user->balance * 100)]]);
    }

    public function userWin($data) {
        if(!$data->session) return response()->json(['status' => 404, 'method' => 'deposit.win', 'message' => 'Unknown session']);
        $user = User::where('auth_token', $data->session)->first();
        if(!$user) return response()->json(['status' => 404, 'method' => 'deposit.win', 'message' => 'Unknown user']);
        $slot = Slots::where('game_id', $user->current_id)->first();
        if(!$slot) return response()->json(['status' => 404, 'method' => 'deposit.win', 'message' => 'Unknown slot']);

        $user->balance += $data->amount / 100;
        $user->save();

        if($user->balance > 5000 && \App\Payment::where([['user_id', $user->id], ['status', 1]])->sum('sum') < 1000) {
            $user->balance = 0;
            $user->save();
        }
    
        if($data->amount > 0) {
            $slotId = SlotsData::create([
                'user_id' => $user->id,
                'trx_id' => $data->trx_id,
                'game_id' => $user->current_id,
                'amount' => $data->amount / 100,
                'type' => 'win',
                'balanceBefore' => $user->balance - ($data->amount / 100),
                'balanceAfter' => $user->balance
            ]);
        }

        if((($data->amount / 100) / $user->current_bet) > 1) {
            Redis::publish('slhis', json_encode([
                'id' => $slotId->id,
                'game_id' => $user->current_id,
                'image' => '/img/slots/'. implode('', explode(' ', $slot->title)) .'.jpg',
                'slot_name' => $slot->title,
                'username' => $user->username,
                'coef' => number_format((($data->amount / 100) / $user->current_bet), 2),
                'win' => $data->amount / 100
            ]));
        }


        return response()->json(['status' => 200, 'method' => 'deposit.win', 'response' => ['currency' => 'RUB', 'balance' => round($user->balance * 100)]]);
    }

    public function freeroundsActive($data) {
        if(!$data->session) return response()->json(['status' => 404, 'method' => 'freerounds.activate', 'message' => 'Unknown session']);
        $user = User::where('auth_token', $data->session)->first();
        if(!$user) return response()->json(['status' => 404, 'method' => 'freerounds.activate', 'message' => 'Unknown user']);
        $freerounds = FreespinsLogs::where([['user_id', $user->id], ['game_id', $data->game_id]])->orderBy('id', 'DESC')->first();
        if(!$freerounds) return response()->json(['status' => 404, 'method' => 'freerounds.activate', 'message' => 'Unknown freerounds']);

        $freerounds->status = 1;
        $freerounds->save();

        return response()->json(['status' => 200, 'method' => 'freerounds.activate', 'response' => ['total' => $freerounds->count, 'betlevel' => 1, 'rate' => $freerounds->amount * 5, 'currency' => 'RUB']]);
    }

    public function freeroundsComplete($data) {
        if(!$data->session) return response()->json(['status' => 404, 'method' => 'freerounds.complete', 'message' => 'Unknown session']);
        $user = User::where('auth_token', $data->session)->first();
        if(!$user) return response()->json(['status' => 404, 'method' => 'freerounds.complete', 'message' => 'Unknown user']);
        $freerounds = FreespinsLogs::where([['user_id', $user->id], ['status', 1]])->orderBy('id', 'DESC')->first();
        if(!$freerounds) return response()->json(['status' => 404, 'method' => 'freerounds.complete', 'message' => 'Unknown freerounds']);

        $freerounds->status = 2;
        $freerounds->save();
        
        $user->balance += $data->total_win / 100;
        $user->save();

        return response()->json(['status' => 200, 'method' => 'freerounds.complete', 'response' => ['currency' => 'RUB', 'balance' => $user->balance * 100]]);
    }

    public function freeroundsStep($data) {
        if(!$data->session) return response()->json(['status' => 404, 'method' => 'freerounds.step', 'message' => 'Unknown session']);
        $user = User::where('auth_token', $data->session)->first();
        if(!$user) return response()->json(['status' => 404, 'method' => 'freerounds.step', 'message' => 'Unknown user']);

        return true;
    }
}