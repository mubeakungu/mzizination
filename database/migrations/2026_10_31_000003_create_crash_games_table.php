<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrashGamesTable extends Migration
{
    public function up()
    {
        Schema::create('crash_games', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('theme', 16)->default('rocket'); // flight|rocket|jet (same maths)
            $table->decimal('amount', 14, 2);
            $table->decimal('auto_cashout', 10, 2)->nullable();
            // Provably fair: hash is shown before the round, seed revealed after.
            $table->string('server_seed', 64);
            $table->string('server_seed_hash', 64);
            $table->string('client_seed', 64);
            $table->unsignedInteger('nonce');
            $table->decimal('crash_point', 12, 2);
            $table->unsignedBigInteger('started_at_ms');
            $table->decimal('cashout_multiplier', 12, 2)->nullable();
            $table->decimal('win', 14, 2)->default(0);
            $table->string('status', 16)->default('active')->index(); // active|cashed|crashed
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('crash_games');
    }
}
