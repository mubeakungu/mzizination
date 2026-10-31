<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSportBetsTable extends Migration
{
    public function up()
    {
        Schema::create('sport_bets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('event_id', 64)->index();
            $table->string('sport_key', 64);
            $table->string('sport_title')->nullable();
            $table->string('home_team');
            $table->string('away_team');
            $table->timestamp('commence_time')->nullable();
            $table->string('selection');
            $table->decimal('odds', 8, 2);
            $table->decimal('amount', 14, 2);
            $table->decimal('potential_win', 14, 2);
            $table->string('status', 16)->default('pending')->index(); // pending|won|lost|void
            $table->string('result')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sport_bets');
    }
}
