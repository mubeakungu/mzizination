<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FreespinsLogs extends Model
{

    protected $fillable = [
        'id',
        'user_id',
        'code',
        'count',
        'amount',
        'game_id',
        'status'
    ];  
}
