<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\TournamentPlayers;

class Tournament extends Model
{
    protected $guarded = [];
    protected $casts = [
        'games' => 'array',
        'awards' => 'array'
    ];
    public $timestamps = false;

    public function count()
    {
        return $this->hasMany(TournamentPlayers::class, 'tournament_id')
            ->select('tournament_id', \DB::raw('count(*) as players'))
            ->groupBy('tournament_id');
    }

    public function leaders()
    {
        return $this->hasMany(TournamentPlayers::class, 'tournament_id')
            ->join('users', 'users.id', '=', 'tournaments_players.player_id')
            ->select('users.id', 'users.username', 'tournaments_players.*')
            ->orderBy('points', 'desc')
            ->limit(5);
    }

    public function players()
    {
        return $this->hasMany(TournamentPlayers::class, 'tournament_id')
            ->join('users', 'users.id', '=', 'tournaments_players.player_id')
            ->select('users.id', 'users.username', 'tournaments_players.*')
            ->orderBy('points', 'desc')
            ->limit(10);
    }
}
