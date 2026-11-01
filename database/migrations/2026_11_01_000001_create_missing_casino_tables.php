<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tables the original casino script used but never shipped migrations for.
 * Columns were taken from the models, controllers, admin forms and Vue pages
 * that read and write them. Each table is only created if it doesn't exist,
 * so this is safe to run against a database that was imported from a dump.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->create('transactions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('action');
            $t->double('amount')->default(0);
            $t->string('type', 16); // up | down
            $t->timestamps();
        });

        // Single row (id = 1): house bank per game and earnings counters.
        $this->create('profit', function (Blueprint $t) {
            $t->id();
            foreach (['dice', 'mines', 'plinko', 'bubbles', 'wheel'] as $g) {
                $t->double('bank_' . $g)->default(0);
                $t->double('earn_' . $g)->default(0);
            }
            $t->double('comission')->default(0);
            $t->timestamps();
        });

        $this->create('mines', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->double('amount');
            $t->integer('bombs');
            $t->integer('step')->default(0);
            $t->json('grid')->nullable();
            $t->integer('status')->default(0);
            $t->integer('fake')->default(0);
            $t->timestamps();
        });

        // Single row: plinko multiplier tables, data[difficulty][pins] = [multipliers].
        $this->create('plinko_data', function (Blueprint $t) {
            $t->id();
            $t->json('data');
            $t->timestamps();
        });

        $this->create('ranks', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('icon')->nullable();
            $t->double('bets')->default(0);     // total staked to reach this rank
            $t->double('deposit')->default(0);  // total deposited to reach this rank
            $t->double('reward')->default(0);   // one-off reward on reaching it
            $t->double('min_bonus')->default(0);
            $t->double('max_bonus')->default(0);
            $t->double('comission')->default(0);
            $t->double('cashback')->default(0);
            $t->timestamps();
        });

        $this->create('bonus_levels', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->integer('goal');
            $t->double('reward')->default(0);
            $t->string('background', 16)->nullable();
            $t->timestamps();
        });

        $this->create('posts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('owner_id')->index();
            $t->string('post_id');
            $t->string('type')->nullable();
            $t->timestamps();
        });

        $this->create('referral_profits', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('from_id')->index();
            $t->unsignedBigInteger('ref_id')->index();
            $t->double('amount')->default(0);
            $t->integer('level')->default(1);
            $t->timestamps();
        });

        $this->create('faqs', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('quest')->nullable();
            $t->timestamps();
        });

        $this->create('withdraw_systems', function (Blueprint $t) {
            $t->id();
            $t->string('sys');
            $t->string('title');
            $t->string('placeholder')->nullable();
            $t->double('comission')->default(0);
            $t->double('min')->default(0);
            $t->double('max')->default(0);
            $t->integer('active')->default(1);
            $t->timestamps();
        });

        $this->create('slots', function (Blueprint $t) {
            $t->id();
            $t->string('game_id')->index();
            $t->string('title');
            $t->string('alias')->nullable();
            $t->string('provider')->nullable()->index();
            $t->string('provider_name')->nullable();
            $t->string('icon')->nullable();
            $t->integer('is_live')->default(0);
            $t->integer('show')->default(1);
            $t->integer('priority')->default(0);
            $t->integer('hot')->default(0);
            $t->integer('new')->default(0);
            $t->timestamps();
        });

        $this->create('slots_data', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('game_id')->nullable();
            $t->string('trx_id')->nullable()->index();
            $t->string('type')->nullable();
            $t->double('amount')->default(0);
            $t->timestamps();
        });

        $this->create('freespins_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('code')->nullable();
            $t->integer('count')->default(0);
            $t->double('amount')->default(0);
            $t->string('game_id')->nullable();
            $t->integer('status')->default(0);
            $t->timestamps();
        });

        $this->create('tournaments', function (Blueprint $t) {
            $t->id();
            $t->string('title', 50);
            $t->json('games')->nullable();
            $t->json('awards')->nullable();
            $t->integer('awards_count')->default(0);
            $t->string('banner', 500)->nullable();
            $t->string('preview', 500)->nullable();
            $t->integer('active')->default(0);
            $t->timestamp('started_at')->nullable();
            $t->timestamp('finished_at')->nullable();
        });

        $this->create('tournaments_players', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tournament_id')->index();
            $t->unsignedBigInteger('player_id')->index();
            $t->double('points')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'tournaments_players', 'tournaments', 'freespins_logs', 'slots_data', 'slots',
            'withdraw_systems', 'faqs', 'referral_profits', 'posts', 'bonus_levels', 'ranks',
            'plinko_data', 'mines', 'profit', 'transactions',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function create(string $table, \Closure $cb): void
    {
        if (!Schema::hasTable($table)) {
            Schema::create($table, $cb);
        }
    }
};
