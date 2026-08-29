<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{
    protected $fillable = [
        'user_id', 
        'sum', 
        'sumWithCom', 
        'wallet', 
        'system', 
        'system_title', 
        'reason', 
        'fake',
        'status'
    ];
}
