<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderTransaction extends Model
{
    protected $guarded = [];
    protected $casts = ['amount' => 'float', 'balance_after' => 'float', 'rolled_back' => 'boolean'];
}
