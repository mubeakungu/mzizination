<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResponsibleGamblingToUsers extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // KYC / age verification
            $table->date('date_of_birth')->nullable();
            $table->string('id_number', 32)->nullable();
            $table->string('kyc_status', 16)->default('none'); // none|pending|verified|rejected
            $table->timestamp('kyc_submitted_at')->nullable();

            // Player-set limits (null = no limit)
            $table->decimal('daily_deposit_limit', 14, 2)->nullable();
            $table->decimal('daily_stake_limit', 14, 2)->nullable();
            // Raising/removing a limit only takes effect after a 24h cooling-off period
            $table->decimal('pending_deposit_limit', 14, 2)->nullable();
            $table->decimal('pending_stake_limit', 14, 2)->nullable();
            $table->boolean('pending_deposit_unlimited')->default(false);
            $table->boolean('pending_stake_unlimited')->default(false);
            $table->timestamp('pending_limits_at')->nullable();

            $table->timestamp('self_excluded_until')->nullable();

            // Running total of stakes for the current day
            $table->date('stake_day')->nullable();
            $table->decimal('stake_today', 14, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth', 'id_number', 'kyc_status', 'kyc_submitted_at',
                'daily_deposit_limit', 'daily_stake_limit', 'pending_deposit_limit', 'pending_stake_limit',
                'pending_deposit_unlimited', 'pending_stake_unlimited', 'pending_limits_at',
                'self_excluded_until', 'stake_day', 'stake_today',
            ]);
        });
    }
}
