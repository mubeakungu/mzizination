<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redis;

use App\User;
use App\Game;
use App\Setting;
use App\Profit;
use App\Transaction;
use App\PlinkoData;
use Auth;
use DB;

class PlinkoController extends Controller
{
    public function getMultipliers()
    {
        $multipliers = PlinkoData::first()->data;
        return $multipliers;
    }

    public function play(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bet' => 'required|numeric|min:1|max:1000000',
            'pins' => 'required|integer|min:8|max:16',
            'difficulty' => [
                Rule::in(['low', 'medium', 'high']),
                'required'
            ],
        ]);

        if($validator->fails()) {
            return [
                'error' => true,
                'message' => $validator->errors()->first()
            ];
        }

        $bet = $request->bet;
        $pins = $request->pins;
        $difficulty = $request->difficulty;

        $user = User::lockForUpdate()->find($this->user->id);

        if($user->balance < $bet) {
            return [
                'error' => true,
                'message' => 'Недостаточно средств'
            ];
        }
        
        $multipliers = $this->getMultipliers()[$difficulty][$pins];
        [$bucketId, $coeff] = $this->generateRandomNumber($multipliers);
        
        $profit = round(($bet * $coeff) - $bet, 2);
        $win = $profit + $bet;

        // антиминус

        $setting = Setting::query()->find(1);
        $antiminus = Profit::query()->find(1);

        if($setting->antiminus == 1 && !$this->user->is_youtuber) {
            if($profit > $antiminus->bank_plinko) {

                $coeff = min($multipliers);
                $bucketId = array_search($coeff, $multipliers);

                $profit = round(($bet * $coeff) - $bet, 2);
                $win = $profit + $bet;

            }
        }

        DB::beginTransaction();

            Transaction::create([
                'user_id' => $user->id,
                'action'  => 'Ставка в PLinko',
                'amount'  => $bet,
                'type'    => 'down'
            ]);

            $user->balance += $profit;
            $user->plinko += $profit;
            $user->save();

            Transaction::create([
                'user_id' => $user->id,
                'action'  => 'Выигрыш в PLinko (x' . $coeff . ')',
                'amount'  => $win,
                'type'    => 'up'
            ]);

            $game = Game::create([
                'user_id' => $user->id,
                'game' => 'plinko',
                'bet' => $bet,
                'chance' => 0,
                'win' => $win,
                'type' => 'win',
            ]);

            if($setting->antiminus == 1 && !$this->user->is_youtuber) {
                $antiminus->bank_plinko += $profit < 1
                    ? ($profit * -1) / 100 * (100 - $antiminus->comission)
                    : -$profit;
            
                $antiminus->save();
            }

        DB::commit();

        if($coeff >= 1) {
            Redis::publish('newGame', json_encode([
                'id' => $game->id,
                'type' => 'plinko',
                'username' => $user->username,
                'amount' => $game->bet,
                'coeff' => $coeff,
                'result' => $win
            ]));
        }

        return [
            'bet' => $bet,
            'bucket' => $bucketId,
            'result' => $win,
            'coeff' => $coeff
        ];
    }

    private function generateRandomNumber(array $numbers) {
        // Вычисляем общую сумму вероятностей
        $totalProbability = 0;
        foreach ($numbers as $number) {
            $totalProbability += 100 / $number;
        }

        // Генерируем случайное число в диапазоне от 0 до общей суммы вероятностей
        $randomValue = mt_rand(0, $totalProbability);

        // Определяем индекс числа, соответствующего сгенерированному значению
        $accumulatedProbability = 0;
        for ($i = 0; $i < count($numbers); $i++) {
            $number = $numbers[$i];
            $probability = 100 / $number;
            $accumulatedProbability += $probability;
            if ($randomValue <= $accumulatedProbability) {
                // Ищем другие возможные индексы с таким же значением
                $duplicateIndices = array_keys($numbers, $number);
                // Выбираем случайный индекс из найденных
                $randomIndex = $duplicateIndices[array_rand($duplicateIndices)];
                return [$randomIndex, $number];
            }
        }

        return null;
    }
}