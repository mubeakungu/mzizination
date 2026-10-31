<?php

namespace App\Services;

use App\ProviderSession;
use App\ProviderTransaction;
use App\Transaction;
use App\User;
use DB;
use Illuminate\Support\Str;

/**
 * Generic seamless wallet for licensed game providers.
 *
 * Every provider has its own protocol (field names, signature scheme, error
 * codes). This class holds the money logic, which is the same for all of
 * them: idempotent bet/win/rollback against the player's balance. A provider
 * adapter only maps its request/response format onto these calls.
 */
class ProviderWallet
{
    public static function provider($key)
    {
        return config("game_providers.providers.$key");
    }

    public static function enabled($key)
    {
        $p = static::provider($key);
        return $p && !empty($p['secret']) && !empty($p['launch_url']);
    }

    /** Games players can see: only providers with credentials configured. */
    public static function catalog()
    {
        $out = [];
        foreach (config('game_providers.providers', []) as $key => $p) {
            if (!static::enabled($key)) continue;
            foreach ($p['games'] as $game => $title) {
                $out[] = ['provider' => $key, 'provider_name' => $p['name'], 'game' => $game, 'title' => $title];
            }
        }
        return $out;
    }

    public static function launchUrl(User $user, $providerKey, $game, $lobby = '/')
    {
        $p = static::provider($providerKey);
        $token = Str::random(48);

        ProviderSession::create([
            'token' => $token,
            'user_id' => $user->id,
            'provider' => $providerKey,
            'game' => $game,
            'expires_at' => now()->addMinutes((int) config('game_providers.session_minutes', 240)),
        ]);

        return strtr($p['launch_url'], [
            '{token}' => $token,
            '{game}' => urlencode($game),
            '{operator}' => urlencode((string) $p['operator']),
            '{currency}' => config('game_providers.currency'),
            '{lang}' => 'en',
            '{lobby}' => urlencode(url($lobby)),
        ]);
    }

    public static function verifySignature($providerKey, $rawBody, $signature)
    {
        $p = static::provider($providerKey);
        if (!$p || empty($p['secret']) || !$signature) return false;
        return hash_equals(hash_hmac('sha256', $rawBody, $p['secret']), strtolower($signature));
    }

    public static function sessionUser($providerKey, $token)
    {
        $s = ProviderSession::where('token', (string) $token)->where('provider', $providerKey)->first();
        if (!$s || $s->expires_at->isPast()) return null;
        return User::find($s->user_id);
    }

    protected static function ok(User $user, $extra = [])
    {
        return array_merge(['status' => 'ok', 'balance' => round((float) $user->balance, 2), 'currency' => config('game_providers.currency')], $extra);
    }

    protected static function fail($code, $message, $extra = [])
    {
        return array_merge(['status' => 'error', 'code' => $code, 'message' => $message], $extra);
    }

    public static function balance(User $user)
    {
        return static::ok($user);
    }

    public static function bet($providerKey, User $user, array $d)
    {
        return DB::transaction(function () use ($providerKey, $user, $d) {
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            $existing = ProviderTransaction::where('provider', $providerKey)->where('transaction_id', $d['transaction_id'])->first();
            if ($existing) {
                return $existing->type === 'bet' ? static::ok($user, ['transaction_id' => $existing->id, 'duplicate' => true])
                                                 : static::fail('DUPLICATE_TRANSACTION', 'Transaction id already used');
            }

            // A rollback for this bet arrived first: never take the money.
            $rolledBackEarly = ProviderTransaction::where('provider', $providerKey)->where('type', 'rollback')
                ->where('reference_transaction_id', $d['transaction_id'])->exists();
            if ($rolledBackEarly) return static::fail('TRANSACTION_ROLLED_BACK', 'Bet was already rolled back');

            $amount = round((float) $d['amount'], 2);
            if ($amount <= 0) return static::fail('INVALID_AMOUNT', 'Amount must be positive');
            if ($user->ban) return static::fail('PLAYER_BLOCKED', 'Account blocked');

            if ($error = ResponsibleGambling::check($user, 'bet', $amount)) {
                return static::fail('LIMIT_REACHED', $error);
            }
            if ($amount > $user->balance) return static::fail('INSUFFICIENT_FUNDS', 'Insufficient balance', ['balance' => round((float) $user->balance, 2)]);

            $user->decrement('balance', $amount);
            $user = $user->fresh();
            ResponsibleGambling::recordStake($user, $amount);

            $tx = static::record($providerKey, 'bet', $user, $d, $amount);
            Transaction::create(['user_id' => $user->id, 'action' => "{$providerKey} {$tx->game} bet (round {$tx->round_id})", 'amount' => $amount, 'type' => 'down']);

            return static::ok($user, ['transaction_id' => $tx->id]);
        });
    }

    public static function win($providerKey, User $user, array $d)
    {
        return DB::transaction(function () use ($providerKey, $user, $d) {
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            $existing = ProviderTransaction::where('provider', $providerKey)->where('transaction_id', $d['transaction_id'])->first();
            if ($existing) {
                return $existing->type === 'win' ? static::ok($user, ['transaction_id' => $existing->id, 'duplicate' => true])
                                                 : static::fail('DUPLICATE_TRANSACTION', 'Transaction id already used');
            }

            $amount = round((float) $d['amount'], 2);
            if ($amount < 0) return static::fail('INVALID_AMOUNT', 'Amount must not be negative');

            // Wins are always paid (even to self-excluded players): the bet was already accepted.
            if ($amount > 0) $user->increment('balance', $amount);
            $user = $user->fresh();

            $tx = static::record($providerKey, 'win', $user, $d, $amount);
            if ($amount > 0) {
                Transaction::create(['user_id' => $user->id, 'action' => "{$providerKey} {$tx->game} win (round {$tx->round_id})", 'amount' => $amount, 'type' => 'up']);
            }

            return static::ok($user, ['transaction_id' => $tx->id]);
        });
    }

    /** Cancels an earlier bet (refund) or win (claw back). */
    public static function rollback($providerKey, User $user, array $d)
    {
        return DB::transaction(function () use ($providerKey, $user, $d) {
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            $existing = ProviderTransaction::where('provider', $providerKey)->where('transaction_id', $d['transaction_id'])->first();
            if ($existing) return static::ok($user, ['transaction_id' => $existing->id, 'duplicate' => true]);

            $ref = ProviderTransaction::where('provider', $providerKey)
                ->where('transaction_id', (string) ($d['reference_transaction_id'] ?? ''))
                ->where('user_id', $user->id)->lockForUpdate()->first();

            $amount = 0;
            if ($ref && !$ref->rolled_back) {
                if ($ref->type === 'bet') {
                    $amount = $ref->amount;
                    $user->increment('balance', $amount);
                } elseif ($ref->type === 'win') {
                    $amount = -$ref->amount;
                    $user->decrement('balance', $ref->amount);
                }
                $ref->update(['rolled_back' => true]);
            }
            // If the referenced bet hasn't arrived yet we still store the rollback,
            // so the late bet is refused (see bet()).

            $user = $user->fresh();
            $tx = static::record($providerKey, 'rollback', $user, $d, abs($amount));
            if ($amount != 0) {
                Transaction::create(['user_id' => $user->id, 'action' => "{$providerKey} rollback of {$ref->type} {$ref->transaction_id}", 'amount' => abs($amount), 'type' => $amount > 0 ? 'up' : 'down']);
            }

            return static::ok($user, ['transaction_id' => $tx->id]);
        });
    }

    protected static function record($providerKey, $type, User $user, array $d, $amount)
    {
        return ProviderTransaction::create([
            'provider' => $providerKey,
            'transaction_id' => (string) $d['transaction_id'],
            'type' => $type,
            'user_id' => $user->id,
            'game' => $d['game'] ?? null,
            'round_id' => isset($d['round_id']) ? (string) $d['round_id'] : null,
            'reference_transaction_id' => isset($d['reference_transaction_id']) ? (string) $d['reference_transaction_id'] : null,
            'amount' => $amount,
            'balance_after' => $user->balance,
        ]);
    }
}
