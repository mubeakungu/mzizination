<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rows the site can't run without: the settings row, the house profit row,
 * plinko multiplier tables, a starting rank ladder (in KES) and one bonus
 * level. Existing rows are never overwritten.
 */
return new class extends Migration
{
    // Original multiplier tables. With PlinkoController's weighting (chance of a
    // bucket is proportional to 1/multiplier) the return to player equals the
    // harmonic mean of each row, which is between 96.6% and 97.2% for every row.
    private const PLINKO = '{"low":{"8":[4.5,2.5,1.3,0.55,0.35,0.55,1.3,2.5,4.5],"9":[4.8,2.9,1.6,0.74,0.38,0.38,0.74,1.6,2.9,4.8],"10":[5.1,3.3,1.9,0.98,0.48,0.326,0.48,0.98,1.9,3.3,5.1],"11":[5.5,3.6,2.2,1.2,0.63,0.35,0.35,0.63,1.2,2.2,3.6,5.5],"12":[5.7,3.9,2.5,1.5,0.8,0.42,0.32,0.42,0.8,1.5,2.5,3.9,5.7],"13":[6.0,4.3,2.9,1.8,1.0,0.53,0.33,0.33,0.53,1.0,1.8,2.9,4.3,6.0],"14":[6.3,4.6,3.2,2.1,1.2,0.68,0.38,0.301,0.38,0.68,1.2,2.1,3.2,4.6,6.3],"15":[6.6,4.9,3.5,2.3,1.5,0.85,0.47,0.31,0.31,0.47,0.85,1.5,2.3,3.5,4.9,6.6],"16":[7.0,5.3,3.8,2.7,1.7,1.1,0.6,0.36,0.27,0.36,0.6,1.1,1.7,2.7,3.8,5.3,7.0]},"medium":{"8":[18.2,8.2,2.8,0.58,0.21,0.58,2.8,8.2,18.2],"9":[20.4,10.2,4.1,1.1,0.26,0.26,1.1,4.1,10.2,20.4],"10":[21.5,11.6,5.3,1.8,0.44,0.199,0.44,1.8,5.3,11.6,21.5],"11":[23.8,13.6,6.9,2.8,0.82,0.23,0.23,0.82,2.8,6.9,13.6,23.8],"12":[24.6,14.9,8.0,3.7,1.3,0.35,0.193,0.35,1.3,3.7,8.0,14.9,24.6],"13":[27.0,17.0,9.8,4.9,2.0,0.64,0.21,0.21,0.64,2.0,4.9,9.8,17.0,27.0],"14":[27.3,17.8,10.8,5.8,2.7,1.0,0.3,0.188,0.3,1.0,2.7,5.8,10.8,17.8,27.3],"15":[29.4,19.8,12.5,7.2,3.6,1.5,0.5,0.2,0.2,0.5,1.5,3.6,7.2,12.5,19.8,29.4],"16":[30.5,21.0,13.7,8.3,4.5,2.1,0.8,0.26,0.184,0.26,0.8,2.1,4.5,8.3,13.7,21.0,30.5]},"high":{"8":[168.5,61.7,15.0,1.4,0.13,1.4,15.0,61.7,168.5],"9":[182.7,75.9,23.5,4.0,0.206,0.206,4.0,23.5,75.9,182.7],"10":[185.6,85.1,31.2,7.6,0.78,0.119,0.78,7.6,31.2,85.1,185.6],"11":[218.3,108.2,45.0,13.9,2.4,0.176,0.176,2.4,13.9,45.0,108.2,218.3],"12":[219.3,115.9,53.1,19.5,4.8,0.53,0.11,0.53,4.8,19.5,53.1,115.9,219.3],"13":[259.6,144.7,71.8,29.9,9.3,1.7,0.155,0.155,1.7,9.3,29.9,71.8,144.7,259.6],"14":[236.0,137.6,72.8,33.4,12.3,3.0,0.37,0.11,0.37,3.0,12.3,33.4,72.8,137.6,236.0],"15":[289.7,175.6,97.9,48.6,20.2,6.3,1.2,0.139,0.139,1.2,6.3,20.2,48.6,97.9,175.6,289.7],"16":[281.6,176.5,102.9,54.4,25.0,9.2,2.3,0.3,0.104,0.3,2.3,9.2,25.0,54.4,102.9,176.5,281.6]}}';

    public function up(): void
    {
        $now = now();

        if (!DB::table('settings')->where('id', 1)->exists()) {
            DB::table('settings')->insert([
                'id' => 1,
                'title' => 'Mzizination',
                'description' => 'Mzizination - casino, crash games and sports betting',
                'min_payment_sum' => 100,
                'min_withdraw_sum' => 100,
                'min_bonus_sum' => 0,
                'bot_timer' => 0,
                'antiminus' => 0,
                'file_version' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (!DB::table('profit')->where('id', 1)->exists()) {
            DB::table('profit')->insert(['id' => 1, 'created_at' => $now, 'updated_at' => $now]);
        }

        if (!DB::table('plinko_data')->exists()) {
            DB::table('plinko_data')->insert(['data' => self::PLINKO, 'created_at' => $now, 'updated_at' => $now]);
        }

        if (!DB::table('ranks')->exists()) {
            $ladder = [
                // name, total staked, total deposited, reward, cashback %
                ['Rookie', 0, 0, 0, 0],
                ['Bronze', 5000, 1000, 50, 1],
                ['Silver', 25000, 5000, 150, 1.5],
                ['Gold', 100000, 20000, 500, 2],
                ['Platinum', 250000, 50000, 1000, 2.5],
                ['Ruby', 500000, 100000, 2000, 3],
                ['Sapphire', 1000000, 200000, 4000, 3.5],
                ['Emerald', 2500000, 500000, 8000, 4],
                ['Diamond', 5000000, 1000000, 15000, 4.5],
                ['Legend', 10000000, 2000000, 30000, 5],
            ];
            foreach ($ladder as $i => [$name, $bets, $deposit, $reward, $cashback]) {
                DB::table('ranks')->insert([
                    'name' => $name,
                    'icon' => "/img/ranks/{$i}.png",
                    'bets' => $bets,
                    'deposit' => $deposit,
                    'reward' => $reward,
                    'min_bonus' => 0,
                    'max_bonus' => 0,
                    'comission' => 0,
                    'cashback' => $cashback,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (!DB::table('bonus_levels')->exists()) {
            DB::table('bonus_levels')->insert([
                'title' => 'Starter', 'goal' => 1, 'reward' => 0, 'background' => '#2f80ed',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Leave data in place; the tables themselves are dropped by 000001.
    }
};
