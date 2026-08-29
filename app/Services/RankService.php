<?php
namespace App\Services;

use App\User;
use App\Rank;
use Redis;
use DB;

class RankService
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function get()
    {
        return $this->calculate($this->user->bets, $this->user->payments());
    }

    public function next()
    {
        $rank = $this->get();

        return Rank::where('bets', '>=', $rank->bets)
            ->where('deposit', '>=', $rank->deposit)
            ->where('id', '!=', $rank->id)
            ->first();
    }

    public function update(User $user)
    {
        $currentRank = $this->get();
        $newRank = $this->calculate($user->bets, $user->payments());

        if($currentRank->id !== $newRank->id)
        {
            $ranks = Rank::where('id', '>', $currentRank->id)
                ->where('id', '<=', $newRank->id)
                ->get();

            foreach($ranks as $rank)
            {
                // соответствует ли пользователь всем условиям
                if($user->bets >= $rank->bets && $user->payments() >= $rank->deposit)
                {
                    $user->increment('balance', $rank->reward);
            
                    Redis::publish('userMessage', json_encode([
                        'user_id' => $user->id,
                        'title' => 'Поздравляем',
                        'type' => 'success',
                        'message' => "Вы достигли ранга <b>{$rank->name}</b>!<br>Начислено <b>{$rank->reward}</b>р"
                    ]));

                    Redis::publish('userRank', json_encode([
                        'user_id' => $user->id,
                        'rank' => $rank,
                        'nextRank' => $this->next()
                    ]));
                }
            }
        }

        return $user;
    }

    public function calculate($bets, $deposit)
    {
        $rank = Rank::orderBy('id', 'desc')
            ->where('bets', '<=', $bets)
            ->where('deposit', '<=', $deposit)
            ->first();
        
        return $rank;
    }
}