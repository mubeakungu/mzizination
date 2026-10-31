<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrashGame extends Model
{
    protected $guarded = [];

    protected $hidden = ['server_seed', 'crash_point'];

    protected $casts = [
        'amount' => 'float',
        'auto_cashout' => 'float',
        'crash_point' => 'float',
        'cashout_multiplier' => 'float',
        'win' => 'float',
        'started_at_ms' => 'integer',
        'nonce' => 'integer',
    ];

    /** Public view: seeds and crash point are revealed only once the round is over. */
    public function toPublic()
    {
        $data = [
            'id' => $this->id,
            'theme' => $this->theme,
            'amount' => $this->amount,
            'auto_cashout' => $this->auto_cashout,
            'status' => $this->status,
            'server_seed_hash' => $this->server_seed_hash,
            'client_seed' => $this->client_seed,
            'nonce' => $this->nonce,
            'started_at_ms' => $this->started_at_ms,
            'cashout_multiplier' => $this->cashout_multiplier,
            'win' => $this->win,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];

        if ($this->status !== 'active') {
            $data['server_seed'] = $this->server_seed;
            $data['crash_point'] = $this->crash_point;
        }

        return $data;
    }
}
