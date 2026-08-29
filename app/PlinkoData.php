<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlinkoData extends Model
{
    protected $guarded = [];
    protected $casts = ['data' => 'array',];
}
