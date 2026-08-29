<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $fillable = [
        'message',
        'total_users',
        'sent_count',
        'failed_count',
        'last_user_id',
        'status',
        'admin_tg_id'
    ];
}
