<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTonPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('ton_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('memo', 32)->unique(); // Уникальный код платежа
            $table->decimal('amount_ton', 18, 8); // Сумма в TON
            $table->decimal('amount_rub', 12, 2); // Сумма в рублях
            $table->decimal('rate', 12, 2); // Курс на момент создания
            $table->string('tx_hash')->nullable(); // Хеш транзакции
            $table->tinyInteger('status')->default(0); // 0=ожидает, 1=подтвержден, 2=переведен
            $table->timestamp('expires_at'); // Время истечения
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['status', 'expires_at']);
            $table->index('memo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ton_payments');
    }
}
