<?php

namespace App\Http\Controllers;

use App\Game;
use App\User;
use App\Mine as Mines;

use App\Helpers\Mine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class FakeController extends Controller
{
    private $mine;

    // Plinko multipliers
    private $plinkoMultipliers = [
        'low' => [
            8 => [5.6, 2.1, 1.1, 1, 0.5, 1, 1.1, 2.1, 5.6],
            9 => [5.6, 2, 1.6, 1, 0.7, 0.7, 1, 1.6, 2, 5.6],
            10 => [8.9, 3, 1.4, 1.1, 1, 0.5, 1, 1.1, 1.4, 3, 8.9],
            11 => [8.4, 3, 1.9, 1.3, 1, 0.7, 0.7, 1, 1.3, 1.9, 3, 8.4],
            12 => [10, 3, 1.6, 1.4, 1.1, 1, 0.5, 1, 1.1, 1.4, 1.6, 3, 10],
            13 => [8.1, 4, 3, 1.9, 1.2, 0.9, 0.7, 0.7, 0.9, 1.2, 1.9, 3, 4, 8.1],
            14 => [7.1, 4, 1.9, 1.4, 1.3, 1.1, 1, 0.5, 1, 1.1, 1.3, 1.4, 1.9, 4, 7.1],
            15 => [15, 8, 3, 2, 1.5, 1.1, 1, 0.7, 0.7, 1, 1.1, 1.5, 2, 3, 8, 15],
            16 => [16, 9, 2, 1.4, 1.4, 1.2, 1.1, 1, 0.5, 1, 1.1, 1.2, 1.4, 1.4, 2, 9, 16]
        ],
        'medium' => [
            8 => [13, 3, 1.3, 0.7, 0.4, 0.7, 1.3, 3, 13],
            9 => [18, 4, 1.7, 0.9, 0.5, 0.5, 0.9, 1.7, 4, 18],
            10 => [22, 5, 2, 1.4, 0.6, 0.4, 0.6, 1.4, 2, 5, 22],
            11 => [24, 6, 3, 1.8, 0.7, 0.5, 0.5, 0.7, 1.8, 3, 6, 24],
            12 => [33, 11, 4, 2, 1.1, 0.6, 0.3, 0.6, 1.1, 2, 4, 11, 33],
            13 => [43, 13, 6, 3, 1.3, 0.7, 0.4, 0.4, 0.7, 1.3, 3, 6, 13, 43],
            14 => [58, 15, 7, 4, 1.9, 1, 0.5, 0.2, 0.5, 1, 1.9, 4, 7, 15, 58],
            15 => [88, 18, 11, 5, 3, 1.3, 0.5, 0.3, 0.3, 0.5, 1.3, 3, 5, 11, 18, 88],
            16 => [110, 41, 10, 5, 3, 1.5, 1, 0.5, 0.3, 0.5, 1, 1.5, 3, 5, 10, 41, 110]
        ],
        'high' => [
            8 => [29, 4, 1.5, 0.3, 0.2, 0.3, 1.5, 4, 29],
            9 => [43, 7, 2, 0.6, 0.2, 0.2, 0.6, 2, 7, 43],
            10 => [76, 10, 3, 0.9, 0.3, 0.2, 0.3, 0.9, 3, 10, 76],
            11 => [120, 14, 5.2, 1.4, 0.4, 0.2, 0.2, 0.4, 1.4, 5.2, 14, 120],
            12 => [170, 24, 8.1, 2, 0.7, 0.2, 0.2, 0.2, 0.7, 2, 8.1, 24, 170],
            13 => [260, 37, 11, 4, 1, 0.2, 0.2, 0.2, 0.2, 1, 4, 11, 37, 260],
            14 => [420, 56, 18, 5, 1.9, 0.3, 0.2, 0.2, 0.2, 0.3, 1.9, 5, 18, 56, 420],
            15 => [620, 83, 27, 8, 3, 0.5, 0.2, 0.2, 0.2, 0.2, 0.5, 3, 8, 27, 83, 620],
            16 => [1000, 130, 26, 9, 4, 2, 0.2, 0.2, 0.2, 0.2, 0.2, 2, 4, 9, 26, 130, 1000]
        ]
    ];

    // Wheel multipliers
    private $wheelMultipliers = [
        1 => [0, 1.2, 1.5], // low risk
        2 => [0, 1.2, 1.5, 3, 5], // medium risk
        3 => [0, 49.5] // high risk
    ];

    public function __construct(Mine $mine)
    {
        $this->mine = $mine;
    }

    public function fake()
    {
        $bot = User::query()->where('is_bot', 1)->inRandomOrder()->first();

        if (!$bot) {
            return 'bot not found';
        }

        $rnd = mt_rand(0, 3);

        switch ($rnd) {
            case 0:
                $this->createDiceGame($bot);
                break;
            case 1:
                $this->createRandomMines($bot);
                break;
            case 2:
                $this->createPlinkoGame($bot);
                break;
            case 3:
                $this->createWheelGame($bot);
                break;
        }

        return 'ok';
    }

    private function createPlinkoGame($bot)
    {
        $bet = mt_rand(10, 500) / 10; // 1-50
        $pins = mt_rand(8, 16);
        $difficulties = ['low', 'medium', 'high'];
        $difficulty = $difficulties[mt_rand(0, 2)];
        
        $multipliers = $this->plinkoMultipliers[$difficulty][$pins];
        $bucketCount = count($multipliers);
        
        // Weighted random - center buckets more likely
        $weights = [];
        $center = ($bucketCount - 1) / 2;
        for ($i = 0; $i < $bucketCount; $i++) {
            $distance = abs($i - $center);
            $weights[$i] = max(1, 10 - $distance * 2);
        }
        
        $totalWeight = array_sum($weights);
        $rand = mt_rand(1, $totalWeight);
        $bucket = 0;
        $cumulative = 0;
        
        foreach ($weights as $i => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                $bucket = $i;
                break;
            }
        }
        
        $coef = $multipliers[$bucket];
        $win = $bet * $coef;
        
        $game = Game::create([
            'user_id' => $bot->id,
            'game' => 'plinko',
            'bet' => $bet,
            'chance' => $pins, // используем для хранения pins
            'win' => $win,
            'type' => $win > $bet ? 'win' : 'lose',
            'fake' => 1
        ]);

        if ($win > 0) {
            Redis::publish('newGame', json_encode([
                'id' => $game->id,
                'type' => 'plinko',
                'username' => $bot->username,
                'amount' => $bet,
                'coeff' => $coef,
                'result' => $win
            ]));
        }
    }

    private function createWheelGame($bot)
    {
        $bet = mt_rand(10, 500) / 10; // 1-50
        $level = mt_rand(1, 3);
        
        $multipliers = $this->wheelMultipliers[$level];
        
        // Random multiplier based on level
        if ($level == 3) {
            // High risk - mostly 0, rarely 49.5
            $coef = mt_rand(1, 100) <= 2 ? 49.5 : 0;
        } else {
            $coef = $multipliers[mt_rand(0, count($multipliers) - 1)];
        }
        
        $win = $bet * $coef;
        
        $game = Game::create([
            'user_id' => $bot->id,
            'game' => 'wheel',
            'bet' => $bet,
            'chance' => $level, // используем для хранения level
            'win' => $win,
            'type' => $win > $bet ? 'win' : 'lose',
            'fake' => 1
        ]);

        if ($win > 0) {
            Redis::publish('newGame', json_encode([
                'id' => $game->id,
                'type' => 'wheel',
                'username' => $bot->username,
                'amount' => $bet,
                'coeff' => $coef,
                'result' => $win
            ]));
        }
    }

    private function createRandomMines($bot)
    {
        $bet = mt_rand(1, 50);
        $bombs = mt_rand(2, 24); // количество бомб
        $bombsPosition = $this->mine->generateBombs($bombs); // позиция бомб на поле

        $usedPosition = [];
        $win = -1;
        $coef = 0;

        while($win == -1) {
            $path = mt_rand(1, 25);
            
            if (count($usedPosition) > 0) {
                $taked = mt_rand(0, 1);

                if ($taked) {
                    $win = 1;
                }
            }

            if(!in_array($path, $usedPosition)) { // нажимал ли бот на эту клетку
                
                $usedPosition[] = $path;
                if(in_array($path, $bombsPosition)) { // бот проиграл
                    $win = 0;
                }

                if(25 - count($usedPosition) == $bombs) {
                    $win = 1;
                }
            }
        }

        if($win) {
            $coef = $this->mine->coef[$bombs][count($usedPosition) - 1];
        }

        $totalWin = $bet * $coef;

        $game = Mines::create([
            'user_id' => $bot->id,
            'amount'  => $bet,
            'bombs'   => $bombs,
            'grid'    => $this->mine->generateBotGrid($bombsPosition, $usedPosition),
            'step'    => count($usedPosition),
            'status'  => 1,
            'fake'    => 1
        ]);

        if($win) {
            Redis::publish('newGame', json_encode([
                'id' => $game->id,
                'type' => 'mines',
                'username' => $bot->username,
                'amount' => $bet,
                'coeff' => round($totalWin / $bet, 2),
                'result' => $totalWin
            ]));
        }
    }

    private function createDiceGame($bot)
    {
        $type = ['min', 'middle', 'max'][rand(0, 2)];
        
        $random = rand(0, 999999);
        $chance = rand(100, 9500) / 100;
        $bet = rand(100, 10000) / 100;

        $min = round(($chance / 100) * 999999, 0);
        $middle['min'] = round((100 - $chance) * 10000 / 2, 0);
        $middle['max'] = round((100 - $chance) * 10000 / 2, 0) + round(($chance / 100) * 999999, 0);
        $max = 999999 - round(($chance / 100) * 999999, 0);

        $win = round((100 / $chance) * $bet, 2);
        $isWin = false;

        switch($type) {
            case 'min':
                if($random <= $min) $isWin = true;
            break;
            case 'middle':
                if($random >= $middle['min'] && $random <= $middle['max']) $isWin = true;
            break;
            case 'max':
                if($random >= $max) $isWin = true;
            break;
        }

        $game = Game::create([
            'user_id' => $bot->id,
            'game' => 'dice',
            'bet' => $bet,
            'chance' => $chance,
            'win' => $isWin ? $win : 0,
            'type' => $isWin ? 'win' : 'lose',
            'fake' => 1
        ]);

        if($isWin) {
            Redis::publish('newGame', json_encode([
                'id' => $game->id,
                'type' => 'dice',
                'username' => $bot->username,
                'amount' => $bet,
                'coeff' => round($win / $bet, 2),
                'result' => $isWin ? $win : 0
            ]));
        }
    }
}
