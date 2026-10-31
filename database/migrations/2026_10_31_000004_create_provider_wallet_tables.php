<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderWalletTables extends Migration
{
    public function up()
    {
        Schema::create('provider_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token', 64)->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('provider', 32);
            $table->string('game', 64);
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::create('provider_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('provider', 32);
            $table->string('transaction_id', 128);
            $table->string('type', 16); // bet|win|rollback
            $table->unsignedBigInteger('user_id')->index();
            $table->string('game', 64)->nullable();
            $table->string('round_id', 128)->nullable();
            $table->string('reference_transaction_id', 128)->nullable();
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->boolean('rolled_back')->default(false);
            $table->timestamps();
            // Providers retry; the same transaction id must never move money twice.
            $table->unique(['provider', 'transaction_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('provider_transactions');
        Schema::dropIfExists('provider_sessions');
    }
}
