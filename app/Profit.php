<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profit extends Model
{
    protected $table = 'profit';

    protected $fillable = [
        'bank_dice', 'bank_mines', 'bank_plinko', 'bank_bubbles', 'bank_wheel', 'comission', 'earn_dice', 'earn_mines', 'earn_bubbles'
    ];
}
