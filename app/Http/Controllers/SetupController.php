<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    public function migrateChat()
    {
        try {
            if (!Schema::hasTable('chats')) {
                Schema::create('chats', function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('user_id');
                    $table->text('message');
                    $table->boolean('fake')->default(0);
                    $table->timestamps();
                });
                return "Table 'chats' created successfully.";
            } else {
                return "Table 'chats' already exists.";
            }
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
