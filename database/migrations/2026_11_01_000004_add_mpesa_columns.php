<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $t) {
            $t->string('phone', 16)->nullable();
            $t->string('checkout_request_id', 64)->nullable()->unique();
            $t->string('mpesa_receipt', 32)->nullable()->unique();
            $t->string('result_desc')->nullable();
        });

        Schema::table('withdraws', function (Blueprint $t) {
            $t->string('conversation_id', 64)->nullable()->index();
            $t->string('originator_conversation_id', 64)->nullable()->unique();
            $t->string('mpesa_receipt', 32)->nullable();
            $t->string('result_desc')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', fn (Blueprint $t) => $t->dropColumn(['phone', 'checkout_request_id', 'mpesa_receipt', 'result_desc']));
        Schema::table('withdraws', fn (Blueprint $t) => $t->dropColumn(['conversation_id', 'originator_conversation_id', 'mpesa_receipt', 'result_desc']));
    }
};
