<?php

namespace App\Services;

use App\Payment;
use App\User;
use Carbon\Carbon;

class ResponsibleGambling
{
    const MIN_AGE = 18;
    const COOLING_OFF_HOURS = 24;
    const EXCLUSION_DAYS = [1, 7, 30, 90, 180, 365, 'permanent'];

    /** Apply limit increases whose cooling-off period has passed. */
    public static function applyPendingLimits(User $user)
    {
        if (!$user->pending_limits_at || Carbon::parse($user->pending_limits_at)->isFuture()) return;

        $update = ['pending_limits_at' => null, 'pending_deposit_limit' => null, 'pending_stake_limit' => null,
                   'pending_deposit_unlimited' => false, 'pending_stake_unlimited' => false];

        if ($user->pending_deposit_unlimited) $update['daily_deposit_limit'] = null;
        elseif ($user->pending_deposit_limit !== null) $update['daily_deposit_limit'] = $user->pending_deposit_limit;

        if ($user->pending_stake_unlimited) $update['daily_stake_limit'] = null;
        elseif ($user->pending_stake_limit !== null) $update['daily_stake_limit'] = $user->pending_stake_limit;

        $user->update($update);
    }

    public static function isSelfExcluded(User $user)
    {
        return $user->self_excluded_until && Carbon::parse($user->self_excluded_until)->isFuture();
    }

    public static function age($dob)
    {
        return $dob ? Carbon::parse($dob)->age : null;
    }

    /** Has the user passed the KYC level required to bet / deposit? */
    public static function kycAllowsPlay(User $user)
    {
        if (static::age($user->date_of_birth) === null || static::age($user->date_of_birth) < static::MIN_AGE) return false;

        $required = config('responsible_gambling.play_requires', 'submitted');
        if ($required === 'verified') return $user->kyc_status === 'verified';
        return in_array($user->kyc_status, ['pending', 'verified']);
    }

    public static function stakedToday(User $user)
    {
        return $user->stake_day && Carbon::parse($user->stake_day)->isToday() ? (float) $user->stake_today : 0.0;
    }

    public static function depositedToday(User $user)
    {
        return (float) Payment::where('user_id', $user->id)->where('status', 1)
            ->where('updated_at', '>=', Carbon::today())->sum('sum');
    }

    /**
     * Returns an error message if the user may not do $action ('bet', 'deposit', 'play'),
     * or null if allowed.
     */
    public static function check(User $user, $action, $amount = 0)
    {
        static::applyPendingLimits($user);

        if (static::isSelfExcluded($user)) {
            return 'You are self-excluded until ' . Carbon::parse($user->self_excluded_until)->toDayDateTimeString() . '.';
        }

        if (!static::kycAllowsPlay($user)) {
            return 'Please verify your age (18+) in Responsible Gaming before playing or depositing.';
        }

        $amount = (float) $amount;

        if ($action === 'bet' && $user->daily_stake_limit !== null) {
            $left = (float) $user->daily_stake_limit - static::stakedToday($user);
            if ($amount > $left) {
                return 'This bet would exceed your daily stake limit. Remaining today: ' . number_format(max(0, $left), 2);
            }
        }

        if ($action === 'deposit' && $user->daily_deposit_limit !== null) {
            $left = (float) $user->daily_deposit_limit - static::depositedToday($user);
            if ($amount > $left) {
                return 'This deposit would exceed your daily deposit limit. Remaining today: ' . number_format(max(0, $left), 2);
            }
        }

        return null;
    }

    public static function recordStake(User $user, $amount)
    {
        $user = $user->fresh();
        if ($user->stake_day && Carbon::parse($user->stake_day)->isToday()) {
            $user->increment('stake_today', (float) $amount);
        } else {
            $user->update(['stake_day' => Carbon::today()->toDateString(), 'stake_today' => (float) $amount]);
        }
    }

    /**
     * Set new limits. Lower limits apply at once; raising or removing one
     * waits COOLING_OFF_HOURS. $limits keys: deposit, stake (null = no limit).
     */
    public static function setLimits(User $user, array $limits)
    {
        static::applyPendingLimits($user);
        $now = [];
        $pending = [];

        foreach (['deposit', 'stake'] as $kind) {
            if (!array_key_exists($kind, $limits)) continue;
            $new = $limits[$kind] === null || $limits[$kind] === '' ? null : round((float) $limits[$kind], 2);
            $current = $user->{"daily_{$kind}_limit"};

            $isDecrease = $new !== null && ($current === null || $new <= (float) $current);
            if ($isDecrease) {
                $now["daily_{$kind}_limit"] = $new;
                $now["pending_{$kind}_limit"] = null;
                $now["pending_{$kind}_unlimited"] = false;
            } elseif ($new !== $current) {
                $pending["pending_{$kind}_limit"] = $new;
                $pending["pending_{$kind}_unlimited"] = $new === null;
            }
        }

        if ($pending) $pending['pending_limits_at'] = now()->addHours(static::COOLING_OFF_HOURS);

        $user->update(array_merge($now, $pending));

        return $user->fresh();
    }

    public static function selfExclude(User $user, $days)
    {
        $until = $days === 'permanent' ? now()->addYears(100) : now()->addDays((int) $days);
        // Never shorten an existing exclusion.
        if (static::isSelfExcluded($user) && Carbon::parse($user->self_excluded_until)->gt($until)) return $user;

        $user->update(['self_excluded_until' => $until]);
        return $user->fresh();
    }

    public static function summary(User $user)
    {
        static::applyPendingLimits($user);
        $user = $user->fresh();

        return [
            'kyc_status' => $user->kyc_status,
            'date_of_birth' => $user->date_of_birth ? Carbon::parse($user->date_of_birth)->toDateString() : null,
            'id_number_masked' => $user->id_number ? str_repeat('*', max(0, strlen($user->id_number) - 3)) . substr($user->id_number, -3) : null,
            'daily_deposit_limit' => $user->daily_deposit_limit,
            'daily_stake_limit' => $user->daily_stake_limit,
            'pending' => $user->pending_limits_at ? [
                'deposit' => $user->pending_deposit_unlimited ? 'none' : $user->pending_deposit_limit,
                'stake' => $user->pending_stake_unlimited ? 'none' : $user->pending_stake_limit,
                'effective_at' => Carbon::parse($user->pending_limits_at)->toIso8601String(),
            ] : null,
            'deposited_today' => static::depositedToday($user),
            'staked_today' => static::stakedToday($user),
            'self_excluded_until' => static::isSelfExcluded($user) ? Carbon::parse($user->self_excluded_until)->toIso8601String() : null,
            'exclusion_options' => static::EXCLUSION_DAYS,
        ];
    }
}
