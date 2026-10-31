<?php

namespace App\Http\Controllers;

use App\CrashGame;
use App\Services\CrashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CrashController extends Controller
{
    public function init()
    {
        $active = CrashGame::where('user_id', $this->user->id)->where('status', 'active')->first();
        if ($active && CrashService::settleIfFinished($active)) $active = null;

        return [
            'active' => $active ? $active->toPublic() : null,
            'history' => $this->history(),
            'server_time_ms' => CrashService::nowMs(),
            'growth' => CrashService::GROWTH,
            'house_edge' => CrashService::edge(),
            'max_multiplier' => CrashService::maxMultiplier(),
            'min_bet' => (float) config('crash.min_bet'),
            'max_bet' => (float) config('crash.max_bet'),
        ];
    }

    public function start(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:' . config('crash.min_bet') . '|max:' . config('crash.max_bet'),
            'auto_cashout' => 'nullable|numeric|min:1.01|max:' . CrashService::maxMultiplier(),
            'client_seed' => 'nullable|string|regex:/^[A-Za-z0-9]{1,64}$/',
            'theme' => 'nullable|in:' . implode(',', CrashService::THEMES),
        ]);

        if ($validator->fails()) {
            return ['error' => true, 'message' => $validator->errors()->first()];
        }

        $auto = $request->auto_cashout ? floor($request->auto_cashout * 100) / 100 : null;

        return CrashService::start($this->user, round($request->amount, 2), $auto, $request->client_seed, $request->theme ?: 'rocket');
    }

    public function cashout(Request $request)
    {
        $game = CrashGame::where('id', (int) $request->id)->where('user_id', $this->user->id)->first();
        if (!$game) return ['error' => true, 'message' => 'Round not found'];

        $game = CrashService::cashout($game);

        return ['game' => $game->toPublic(), 'balance' => $this->user->fresh()->balance];
    }

    public function status(Request $request)
    {
        $game = CrashGame::where('id', (int) $request->id)->where('user_id', $this->user->id)->first();
        if (!$game) return ['error' => true, 'message' => 'Round not found'];

        CrashService::settleIfFinished($game);
        $game = $game->fresh();

        $now = CrashService::nowMs();
        return [
            'game' => $game->toPublic(),
            'server_time_ms' => $now,
            'multiplier' => $game->status === 'active' ? CrashService::multiplierAt($now - $game->started_at_ms) : null,
            'balance' => $this->user->fresh()->balance,
        ];
    }

    protected function history()
    {
        return CrashGame::where('user_id', $this->user->id)->where('status', '!=', 'active')
            ->orderByDesc('id')->limit(20)->get()->map->toPublic();
    }
}
