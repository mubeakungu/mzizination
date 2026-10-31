<?php

namespace App\Services;

use App\CrashGame;
use App\Game;
use App\Transaction;
use App\User;
use DB;

/**
 * Single-player crash rounds.
 *
 * Fairness: crash point = f(HMAC_SHA256(server_seed, "client_seed:nonce")).
 * The server seed's SHA-256 hash is shown before the round and the seed is
 * revealed after, so players can check that the result wasn't changed.
 *
 * With h = the first 52 bits of the HMAC (uniform in [0, 2^52)):
 *     crash = floor( (1 - edge) * 2^52 / (2^52 - h) * 100 ) / 100, min 1.00
 * so P(crash >= x) = (1 - edge) / x for x >= 1, giving RTP = 1 - edge at
 * every cash-out target.
 *
 * Multiplier over time (server clock): m(t) = e^(GROWTH * t_ms), floored to 2 dp.
 */
class CrashService
{
    const GROWTH = 0.00006;  // ~2x after 11.6s, ~10x after 38s

    /** @var callable|null test hook returning "now" in milliseconds */
    public static $clock = null;

    public static function nowMs()
    {
        return static::$clock ? (int) call_user_func(static::$clock) : (int) floor(microtime(true) * 1000);
    }

    public static function edge()
    {
        return (float) config('crash.house_edge', 0.03);
    }

    public static function maxMultiplier()
    {
        return (float) config('crash.max_multiplier', 1000);
    }

    public static function crashPoint($serverSeed, $clientSeed, $nonce, $edge = null)
    {
        $edge = $edge === null ? static::edge() : $edge;
        $hash = hash_hmac('sha256', $clientSeed . ':' . $nonce, $serverSeed);
        $h = hexdec(substr($hash, 0, 13)); // 52 bits
        $e = 4503599627370496;             // 2^52

        $point = floor((1 - $edge) * $e / ($e - $h) * 100) / 100;

        return max(1.00, min($point, static::maxMultiplier()));
    }

    public static function multiplierAt($elapsedMs)
    {
        if ($elapsedMs <= 0) return 1.00;
        return floor(exp(static::GROWTH * $elapsedMs) * 100) / 100;
    }

    /** Milliseconds after start at which the multiplier reaches $m. */
    public static function msToReach($m)
    {
        return (int) ceil(log($m) / static::GROWTH);
    }

    const THEMES = ['flight', 'rocket', 'jet'];

    public static function start(User $user, $amount, $autoCashout = null, $clientSeed = null, $theme = 'rocket')
    {
        return DB::transaction(function () use ($user, $amount, $autoCashout, $clientSeed, $theme) {
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            // Settle anything left over first; only one live round per player.
            foreach (CrashGame::where('user_id', $user->id)->where('status', 'active')->get() as $g) {
                static::settleIfFinished($g);
            }
            if (CrashGame::where('user_id', $user->id)->where('status', 'active')->exists()) {
                return ['error' => true, 'message' => 'Finish your current round first'];
            }

            if ($user->ban) return ['error' => true, 'message' => 'Your account is blocked'];
            if ($amount > $user->balance) return ['error' => true, 'message' => 'Insufficient balance'];

            $serverSeed = bin2hex(random_bytes(32));
            $clientSeed = $clientSeed ?: bin2hex(random_bytes(8));
            $nonce = CrashGame::where('user_id', $user->id)->count() + 1;

            $user->decrement('balance', $amount);

            $game = CrashGame::create([
                'user_id' => $user->id,
                'theme' => in_array($theme, static::THEMES) ? $theme : 'rocket',
                'amount' => $amount,
                'auto_cashout' => $autoCashout,
                'server_seed' => $serverSeed,
                'server_seed_hash' => hash('sha256', $serverSeed),
                'client_seed' => $clientSeed,
                'nonce' => $nonce,
                'crash_point' => static::crashPoint($serverSeed, $clientSeed, $nonce),
                'started_at_ms' => static::nowMs(),
                'status' => 'active',
            ]);

            Transaction::create([
                'user_id' => $user->id,
                'action' => "Crash bet #{$game->id}",
                'amount' => $amount,
                'type' => 'down',
            ]);

            return ['game' => $game->toPublic(), 'server_time_ms' => static::nowMs(), 'balance' => $user->fresh()->balance];
        });
    }

    /**
     * Player presses Cash out. The multiplier is computed from the server clock,
     * never sent by the client.
     */
    public static function cashout(CrashGame $game)
    {
        return DB::transaction(function () use ($game) {
            $game = CrashGame::where('id', $game->id)->lockForUpdate()->first();
            if ($game->status !== 'active') return $game;

            if (static::settleIfFinished($game)) return $game->fresh();

            $m = static::multiplierAt(static::nowMs() - $game->started_at_ms);
            if ($m >= $game->crash_point) {
                static::finish($game, 'crashed');
            } else {
                static::finish($game, 'cashed', $m);
            }

            return $game->fresh();
        });
    }

    /**
     * Settles a round whose outcome is already decided by time:
     * auto cash-out reached before the crash, or the crash happened.
     * Returns true if the round was settled.
     */
    public static function settleIfFinished(CrashGame $game)
    {
        if ($game->status !== 'active') return true;

        $elapsed = static::nowMs() - $game->started_at_ms;
        $auto = $game->auto_cashout;

        if ($auto && $auto < $game->crash_point && $elapsed >= static::msToReach($auto)) {
            static::finish($game, 'cashed', $auto);
            return true;
        }

        if ($elapsed >= static::msToReach($game->crash_point)) {
            static::finish($game, 'crashed');
            return true;
        }

        return false;
    }

    protected static function finish(CrashGame $game, $status, $multiplier = null)
    {
        $win = $status === 'cashed' ? round($game->amount * $multiplier, 2) : 0;
        $maxWin = (float) config('crash.max_win', 0);
        if ($maxWin > 0) $win = min($win, $maxWin);

        $game->update(['status' => $status, 'cashout_multiplier' => $multiplier, 'win' => $win]);

        if ($win > 0) {
            User::where('id', $game->user_id)->lockForUpdate()->first()->increment('balance', $win);
            Transaction::create([
                'user_id' => $game->user_id,
                'action' => "Crash win #{$game->id} (x{$multiplier})",
                'amount' => $win,
                'type' => 'up',
            ]);
        }

        Game::create([
            'user_id' => $game->user_id,
            'game' => 'crash',
            'bet' => $game->amount,
            'chance' => $multiplier ?: $game->crash_point,
            'win' => $win,
            'type' => $win > 0 ? 'win' : 'lose',
            'fake' => 0,
        ]);
    }
}
