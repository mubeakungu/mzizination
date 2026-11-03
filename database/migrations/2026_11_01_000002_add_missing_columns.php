<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Columns the code reads and writes on existing tables that the original
 * 2020 migrations never added. Every column is only added if missing, so
 * this is safe on a database imported from a dump of the original script.
 */
return new class extends Migration
{
    private array $columns = [
        'users' => [
            'unique_id' => ['string', 32, true],
            'avatar' => ['text', null, true],
            'ban' => ['integer', 0],
            'is_youtuber' => ['integer', 0],
            'is_worker' => ['integer', 0],
            'is_manager' => ['integer', 0],
            'is_admin' => ['integer', 0],
            'bets' => ['double', 0],
            'wager' => ['double', 0],
            'wager_status' => ['integer', 0],
            'bonus_balance' => ['double', 0],
            'bonus_daily' => ['bigInteger', 0],
            'bonus_use' => ['integer', 0],
            'total_reposts' => ['integer', 0],
            'referral_send' => ['integer', 0],
            'referral_balance' => ['double', 0],
            'cashback_balance' => ['double', 0],
            'ref_1_lvl' => ['double', 0],
            'ref_2_lvl' => ['double', 0],
            'ref_3_lvl' => ['double', 0],
            // per-game net result counters shown in the admin panel
            'mines' => ['double', 0],
            'bubbles' => ['double', 0],
            'wheel' => ['double', 0],
            'plinko' => ['double', 0],
            'slots' => ['double', 0],
            'tg_id' => ['string', 64, true],
            'telegram_link_code' => ['string', 64, true],
            'auth_token' => ['string', 80, true],
            'game_token' => ['string', 80, true],
            'game_token_date' => ['timestamp', null, true],
            'current_id' => ['string', 64, true],
            'current_bet' => ['double', 0],
        ],
        'settings' => [
            'vk_id' => ['string', 64, true],
            'vk_url' => ['string', 255, true],
            'vk_token' => ['text', null, true],
            'vk_service_token' => ['text', null, true],
            'tg_channel' => ['string', 255, true],
            'tg_bot' => ['string', 255, true],
            'telegram_token' => ['string', 255, true],
            'telegram_chat_id' => ['string', 64, true],
            'kassa_id' => ['string', 255, true],
            'kassa_key' => ['string', 255, true],
            'kassa_secret1' => ['string', 255, true],
            'kassa_secret2' => ['string', 255, true],
            'wallet_id' => ['string', 255, true],
            'wallet_secret' => ['string', 255, true],
            'wallet_desc' => ['string', 255, true],
            'slots_provider' => ['string', 64, true],
            'b2b_provider_id' => ['string', 255, true],
            'b2b_provider_yt_id' => ['string', 255, true],
            'tbs_provider_id' => ['string', 255, true],
            'tbs_provider_secret' => ['string', 255, true],
            'referral_domain' => ['string', 255, true],
            'referral_reward' => ['double', 0],
            'daily_bonus_min' => ['double', 0],
            'daily_bonus_max' => ['double', 0],
            'hourly_bonus_min' => ['double', 0],
            'hourly_bonus_max' => ['double', 0],
            'onetime_bonus' => ['double', 0],
            'min_dep_withdraw' => ['double', 0],
            'deposit_per_n' => ['double', 0],
            'deposit_sum_n' => ['double', 0],
            'withdraw_request_limit' => ['integer', 0],
            'antiminus' => ['integer', 0],
            'file_version' => ['integer', 1],
        ],
        'games' => [
            'fake' => ['integer', 0],
        ],
        'payments' => [
            'method' => ['string', 32, true],
            'bonus' => ['double', 0],
            'fake' => ['integer', 0],
        ],
        'withdraws' => [
            'sumWithCom' => ['double', 0],
            'fake' => ['integer', 0],
            'reason' => ['string', 255, true],
        ],
        'promocodes' => [
            'wager' => ['double', 0],
            'type' => ['string', 16, true], // 'balance' or 'deposit'
        ],
    ];

    public function up(): void
    {
        foreach ($this->columns as $table => $cols) {
            $missing = array_filter(array_keys($cols), fn ($c) => !Schema::hasColumn($table, $c));
            if (!$missing) continue;

            Schema::table($table, function (Blueprint $t) use ($cols, $missing) {
                foreach ($missing as $name) {
                    [$type, $arg] = $cols[$name];
                    $nullable = $cols[$name][2] ?? false;
                    $col = match ($type) {
                        'string' => $t->string($name, $arg),
                        'double' => $t->double($name),
                        default => $t->{$type}($name),
                    };
                    if ($nullable) {
                        $col->nullable();
                    } else {
                        $col->default($arg);
                    }
                }
            });
        }

        // Users created through VK login have no password, and nothing sets is_vk.
        Schema::table('users', function (Blueprint $t) {
            $t->text('password')->nullable()->change();
        });

        // The code compares these flags with 0/1. PostgreSQL won't compare a
        // boolean with an integer, so store them as integers like the rest.
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            foreach (['is_vk', 'is_bot', 'mines_is_active'] as $col) {
                if (Schema::getColumnType('users', $col) === 'bool') {
                    DB::statement("ALTER TABLE users ALTER COLUMN \"$col\" DROP DEFAULT, ALTER COLUMN \"$col\" TYPE integer USING \"$col\"::integer, ALTER COLUMN \"$col\" SET DEFAULT 0");
                }
            }
        } else {
            Schema::table('users', fn (Blueprint $t) => $t->boolean('is_vk')->default(0)->change());
        }

        // The 2020 migration made users.dice JSON, but every game treats it as a
        // numeric counter (increment/decrement). Swap it for a number.
        if (Schema::getColumnType('users', 'dice') === 'json') {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('dice'));
            Schema::table('users', fn (Blueprint $t) => $t->double('dice')->default(0));
        }

        // withdraws.system holds a payment system name ('mpesa', 'card', ...), not a number.
        if (in_array(Schema::getColumnType('withdraws', 'system'), ['int4', 'int8', 'integer', 'int'], true)) {
            Schema::table('withdraws', fn (Blueprint $t) => $t->string('system', 32)->change());
        }

        // users.referral_use holds the inviter's user id (0 for none) and is
        // compared with ids, which PostgreSQL refuses to do against a JSON column.
        if (Schema::getColumnType('users', 'referral_use') === 'json') {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('referral_use'));
            Schema::table('users', fn (Blueprint $t) => $t->unsignedBigInteger('referral_use')->nullable()->index());
        }
    }

    public function down(): void
    {
        foreach ($this->columns as $table => $cols) {
            Schema::table($table, function (Blueprint $t) use ($table, $cols) {
                $t->dropColumn(array_values(array_filter(array_keys($cols), fn ($c) => Schema::hasColumn($table, $c))));
            });
        }
    }
};
