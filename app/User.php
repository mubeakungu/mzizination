<?php

namespace App;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Payment;
use App\Rank;

class User extends Authenticatable
{
    protected $guarded = [];

    public function payments()
    {
        return $this->hasMany(Payment::class)
            ->where('status', 1)
            ->sum('sum');
    }
}
