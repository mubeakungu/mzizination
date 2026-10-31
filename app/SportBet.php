<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SportBet extends Model
{
    protected $guarded = [];

    protected $casts = [
        'commence_time' => 'datetime',
        'settled_at' => 'datetime',
        'amount' => 'float',
        'odds' => 'float',
        'potential_win' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
