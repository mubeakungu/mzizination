<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redis;

use App\User;
use App\Profit;
use App\Transaction;
use DB;

class BubblesController extends Controller
{
    protected $profit;

    public function __construct()
    {
        parent::__construct();
        $this->profit = Profit::first();
    }

    public function play(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bet' => 'required|numeric|min:1|max:1000',
            'current' => 'required|numeric|min:1.05|max:1000',
        ]);

        if($request->bet < 1) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма игры 1 монет'
            ];
        }
        if($request->bet < 1) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма игры 1 монет'
            ];
        }
        if($request->current < 1.05) {
            return [
                'error' => true,
                'message' => 'Минимальная цель игры 1.05'
            ];
        }
        if($request->current > 1000) {
            return [
                'error' => true,
                'message' => 'Максимальная цель игры 1000'
            ];
        }
        $bet = $request->bet;
        $current = $request->current;

        $random = rand(0, 999999);
        $chance = 100 / $current;

        $win = ($bet * $current) - $bet;
        $isWin = false;
        $coef = round(1000000 / ($random + 1), 2);

        if($coef >= $current) $isWin = true;

        try {
            DB::beginTransaction();

            $user = User::where('id', $this->user->id)->lockForUpdate()->first();

            if($user->balance < $bet) {
                return [
                    'error' => true,
                    'message' => 'Недостаточно средств'
                ];
            }

            if($this->config->antiminus == 1 && !$user->is_youtuber) {
                if($win > $this->profit->bank_bubbles) {
                    $coef = round(rand(100, $current * 100 - 1) / 100, 2);
                    $isWin = false;
                }
            }

            // Списание wager при цели от x2
            if($current >= 2) {
                $user->decrement('wager', $bet);
                if($user->wager < 0) $user->update([
                    'wager' => 0
                ]);
            }

            if($isWin) {
                $user->increment('balance', $win);
                $user->increment('bubbles', $win);

                if($this->config->antiminus == 1 && !$user->is_youtuber) {
                    $this->profit->update([
                        'bank_bubbles' => $this->profit->bank_bubbles - $win,
                    ]);
                }

                $text = 'Выигрыш ' . number_format($win + $bet, 2, '.', '');

                Transaction::create([
                    'user_id' => $this->user->id,
                    'action'  => 'Выигрыш в Bubbles',
                    'amount'  => $win,
                    'type'    => 'up'
                ]);
            } else {
                $user->decrement('balance', $bet);
                $user->decrement('bubbles', $bet);

                if(!$user->is_youtuber) {
                    $this->profit->update([
                        'bank_bubbles' => $this->profit->bank_bubbles + ($bet / 100) * (100 - $this->profit->comission),
                        'earn_bubbles' => $this->profit->earn_bubbles + ($bet / 100) * $this->profit->comission
                    ]);
                }

                $text = number_format($coef, 2, '.', '');

                Transaction::create([
                    'user_id' => $this->user->id,
                    'action'  => 'Проигрыш в Bubbles',
                    'amount'  => $bet,
                    'type'    => 'down'
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

        if($isWin) {
            Redis::publish('newGame', json_encode([
                'id' => rand(10000, 99999999999),
                'type' => 'bubbles',
                'username' => $user->username,
                'amount' => $bet,
                'coeff' => round(($win + $bet) / $bet, 2),
                'result' => $isWin ? ($win + $bet) : 0
            ]));
        }
        if($isWin == true) {
            $type = 1;
        } else {
            $type = 0;
        }
        return [
            'game' => [
                'type' => $type,
                'win'  => $coef
            ],
            'balance' => $user->balance,
            'success' => true
        ];
    }
}