<?php

namespace App;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Payment;
use App\Rank;

class User extends Authenticatable
{
    protected $guarded = [];

    // PostgreSQL returns double precision columns as strings; the Vue pages do
    // arithmetic on these, so make sure they arrive as numbers.
    protected $casts = [
        'balance' => 'float',
        'wager' => 'float',
        'bets' => 'float',
        'bonus_balance' => 'float',
        'referral_balance' => 'float',
        'cashback_balance' => 'float',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class)
            ->where('status', 1)
            ->sum('sum');
    }
}
