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

use Auth;
use DB;

class WheelController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->profit = Profit::first();
    }

    public function play(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bet' => 'required|numeric|min:1|max:1000000',
            'level' => 'required|integer|min:1|max:3',
        ]);

        if($validator->fails()) {
            return [
                'error' => true,
                'message' => $validator->errors()->first()
            ];
        }

        $items = [];

        switch ($request->level) {
            case 1:
                for($i = 0; $i <= 10; $i++) $items[] = 'lose';
                for($i = 0; $i <= 5; $i++) $items[] = 'red';
                for($i = 0; $i <= 35; $i++) $items[] = 'blue';
            break;

            case 2: 
                for($i = 0; $i <= 25; $i++) $items[] = 'lose';
                for($i = 0; $i <= 13; $i++) $items[] = 'blue';
                for($i = 0; $i <= 8; $i++) $items[] = 'red';
                for($i = 0; $i <= 3; $i++) $items[] = 'green';
                for($i = 0; $i <= 1; $i++) $items[] = 'pink';
            break;

            case 3:
                for($i = 0; $i <= 49; $i++) $items[] = 'lose';
                for($i = 0; $i <= 1; $i++) $items[] = 'pink';
            break;
        }

        shuffle($items);

        $color = $items[array_rand($items)];
        $coef = $this->getCoef($request->level, $color);

        $totalWin = round($request->bet * $coef - $request->bet, 2);

        if($totalWin > $this->profit->bank_wheel && !$this->user->is_youtuber) {
            $color = 'lose';
            $coef = 0;
            $totalWin = -$request->bet;
        }

        DB::beginTransaction();

        $user = User::lockForUpdate()->where('id', $this->user->id)->first();

        if($user->balance < $request->bet) {
            return [
                'error' => true,
                'message' => 'Недостаточно средств на вашем балансе.'
            ];
        }

        $user->balance += $totalWin;
        $user->wheel += $totalWin;

        $user->wager -= $request->bet;

        if($user->wager < 0) {
            $user->wager = 0;
        }

        $user->save();

        DB::commit();

        $totalWin += $request->bet;

        if($coef) {
            if(!$user->is_youtuber) {
                $this->profit->bank_wheel -= $totalWin - $request->bet;
                $this->profit->save();
            }
            Redis::publish('newGame', json_encode([
                'id' => 'wheel_' . rand(-11111111111111, 999999999999999),
                'type' => 'wheel',
                'username' => $user->username,
                'amount' => $request->bet,
                'coeff' => $coef,
                'result' => $totalWin
            ]));

            Transaction::create([
                'user_id' => $this->user->id,
                'action'  => 'Выигрыш в Wheel',
                'amount'  => $totalWin,
                'type'    => 'up'
            ]);

        } else {
            if(!$user->is_youtuber) {
                $this->profit->bank_wheel += $request->bet / 100 * (100 - $this->profit->comission);
                $this->profit->save();
            }

            Transaction::create([
                'user_id' => $this->user->id,
                'action'  => 'Проигрыш в Wheel',
                'amount'  => $request->bet,
                'type'    => 'down'
            ]);
        }

        return [
            'balance' => $user->balance,
            'color' => $color,
            'win' => round($totalWin, 2),
            'coef' => $coef
        ];
    }

    private function getCoef($level, $color)
    {
        $info = [
            [],
            [
                'blue' => 1.2,
                'red' => 1.5,
                'lose' => 0
            ],
            [
                'blue' => 1.2,
                'red' => 1.5,
                'green' => 3,
                'pink' => 5,
                'lose' => 0  
            ],
            [
                'pink' => 49.5,
                'lose' => 0
            ]
        ];

        return $info[$level][$color];
    }
}