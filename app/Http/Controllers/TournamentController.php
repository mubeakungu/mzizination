<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;

use App\Tournament;
use App\TournamentPlayers;
use App\User;
use DB;

class TournamentController extends Controller
{
    public function init()
    {
        // Временно скрыто
        return [
            'active' => [],
            'soon' => [],
            'finished' => [],
            'now' => Carbon::now()
        ];
        
        /* Раскомментировать когда нужно показать турниры
        $now = Carbon::now();

        $active = Tournament::query()
            ->where('started_at', '<=', $now)
            ->where('finished_at', '>', $now)
            ->get()
            ->map(function ($tournament) {
                return $this->formatTournament($tournament);
            })
            ->values()
            ->all();

        $soon = Tournament::query()
            ->where('started_at', '>', $now)
            ->get()
            ->map(function ($tournament) {
                return $this->formatTournament($tournament);
            })
            ->values()
            ->all();

        $finished = Tournament::query()
            ->where('finished_at', '<', $now)
            ->get()
            ->map(function ($tournament) {
                return $this->formatTournament($tournament);
            })
            ->values()
            ->all();

        return [
            'active' => $active,
            'soon' => $soon,
            'finished' => $finished,
            'now' => $now
        ];
        */
    }

    private function formatTournament($tournament)
    {
        // Получаем лидеров
        $leaders = TournamentPlayers::where('tournament_id', $tournament->id)
            ->join('users', 'users.id', '=', 'tournaments_players.player_id')
            ->select('users.id', 'users.username', 'tournaments_players.*')
            ->orderBy('points', 'desc')
            ->limit(5)
            ->get();

        // Получаем количество участников
        $count = TournamentPlayers::where('tournament_id', $tournament->id)->count();

        // Преобразуем в массив и добавляем дополнительные поля
        $data = $tournament->toArray();
        $data['leaders'] = $leaders->toArray();
        $data['count'] = [['players' => $count]];
        
        // Убеждаемся, что games и awards - это массивы, а не строки
        if (is_string($data['games'])) {
            $data['games'] = json_decode($data['games'], true);
        }
        if (is_string($data['awards'])) {
            $data['awards'] = json_decode($data['awards'], true);
        }

        return $data;
    }

    public function join($id)
    {
        try {
            // Парсим ID, если передан в формате "1:active" или "1:soon"
            $tournamentId = explode(':', $id)[0];
            
            if (!$this->user) {
                return response()->json([
                    'error' => true,
                    'message' => 'Необходимо авторизоваться'
                ], 401);
            }

            $tournament = Tournament::find($tournamentId);

            if (!$tournament) {
                return response()->json([
                    'error' => true,
                    'message' => 'Турнир не найден'
                ], 404);
            }

            if($tournament->finished_at < Carbon::now()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Турнир уже завершен'
                ], 400);
            }

            if(!$tournament->active) {
                return response()->json([
                    'error' => true,
                    'message' => 'Турнир еще не начался'
                ], 400);
            }

            $isExists = $this->joined($tournament->id);

            if($isExists) {
                return response()->json([
                    'error' => true,
                    'message' => 'Вы уже участвуете'
                ], 400);
            }

            TournamentPlayers::create([
                'player_id' => $this->user->id,
                'tournament_id' => $tournament->id,
            ]);

            return response()->json(['join' => true]);
        } catch (\Exception $e) {
            \Log::error('Tournament join error: ' . $e->getMessage(), [
                'id' => $id,
                'user_id' => $this->user ? $this->user->id : null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => 'Произошла ошибка при присоединении к турниру'
            ], 500);
        }
    }

    public function getInfo($id)
    {
        try {
            // Парсим ID, если передан в формате "1:active" или "1:soon"
            $tournamentId = explode(':', $id)[0];
            
            $tournament = Tournament::where('id', $tournamentId);

            if(!$tournament->first()) {
                return response()->json([
                    'error' => true, 
                    'message' => 'Турнир не найден'
                ], 404);
            }
            
            $info = $tournament->with('players')->first();
            $joined = $this->joined($tournamentId);
            $position = [];

            if($this->user && $this->user->id && $joined) {
                $items = TournamentPlayers::where('tournament_id', $tournamentId)
                    ->orderBy('points', 'desc')
                    ->get();
                
                $player = $items->where('player_id', $this->user->id)->first();
                
                if ($player) {
                    $position = [
                        'number' => $this->getPosition($items->toArray()),
                        'points' => $player->points
                    ];
                }
            }

            return response()->json([
                'info' => $info,
                'joined' => $joined,
                'position' => $position
            ]);
        } catch (\Exception $e) {
            \Log::error('Tournament getInfo error: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => 'Произошла ошибка при загрузке информации о турнире'
            ], 500);
        }
    }

    private function joined($tour_id)
    {
        if (!$this->user) {
            return false;
        }

        return TournamentPlayers::where('player_id', $this->user->id)
            ->where('tournament_id', $tour_id)
            ->first();
    }

    private function getPosition($data)
    {
        if (!$this->user) {
            return 0;
        }

        $position = 0;

        foreach ($data as $key => $item) {
            if (isset($item['player_id']) && $item['player_id'] == $this->user->id) {
                $position = $key;
                break;
            }
        }

        return $position + 1;
    }
}