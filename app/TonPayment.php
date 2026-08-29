<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TonPayment extends Model
{
    protected $fillable = [
        'user_id',
        'memo',
        'amount_ton',
        'amount_rub',
        'rate',
        'tx_hash',
        'status',
        'expires_at'
    ];

    protected $casts = [
        'amount_ton' => 'decimal:8',
        'amount_rub' => 'decimal:2',
        'rate' => 'decimal:2',
        'expires_at' => 'datetime'
    ];

    // Статусы
    const STATUS_PENDING = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_TRANSFERRED = 2;
    const STATUS_EXPIRED = 3;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    // Генерация уникального memo
    public static function generateMemo($userId)
    {
        $prefix = 'PAY';
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        return "{$prefix}_{$userId}_{$random}";
    }
}
