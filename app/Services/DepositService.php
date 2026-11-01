<?php

namespace App\Services;

use App\Http\Controllers\TelegramController;
use App\Payment;
use App\ReferralProfit;
use App\Transaction;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Credits a deposit exactly once, whichever gateway reports it.
 * (Moved out of PaymentController so M-Pesa, FreeKassa and RubPay share it.)
 */
class DepositService
{
    /** Default referral percentages per level, overridable per user (ref_N_lvl). */
    private const REFERRAL_PERCENT = [1 => 10, 2 => 3, 3 => 2];

    /**
     * @return string|null error text, or null when the deposit was credited
     */
    public static function complete(int $paymentId, float $paidAmount, array $extra = []): ?string
    {
        $credited = null;

        $error = DB::transaction(function () use ($paymentId, $paidAmount, $extra, &$credited) {
            $payment = Payment::where('id', $paymentId)->lockForUpdate()->first();

            if (!$payment) return 'payment not found';
            if ((int) $payment->status === 1) return 'payment already paid';
            if ($payment->sum > $paidAmount) return 'wrong sum';

            $credit = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;

            $user = User::where('id', $payment->user_id)->lockForUpdate()->first();
            $user->increment('balance', $credit);
            $user->increment('wager', $credit * 2);

            if ($user->referral_use) {
                self::payReferrals($user, $payment->sum);
            }

            $payment->fill($extra);
            $payment->status = 1;
            $payment->save();

            Transaction::create([
                'user_id' => $user->id,
                'action' => 'Deposit' . ($payment->method ? ' (' . $payment->method . ')' : ''),
                'amount' => $credit,
                'type' => 'up',
            ]);

            $credited = [$user, $payment];
            return null;
        });

        if ($credited) {
            [$user, $payment] = $credited;
            try {
                TelegramController::notifyDeposit($user->id, $user->username, $payment->sum, $payment->method ?? 'unknown');
            } catch (\Throwable $e) {
                Log::warning('Deposit notification failed', ['error' => $e->getMessage()]);
            }
        }

        return $error;
    }

    private static function payReferrals(User $user, float $amount): void
    {
        $current = $user;
        foreach (self::REFERRAL_PERCENT as $level => $default) {
            $referrer = $current->referral_use ? User::find($current->referral_use) : null;
            if (!$referrer) return;

            $percent = $referrer->{'ref_' . $level . '_lvl'} > 0 ? $referrer->{'ref_' . $level . '_lvl'} : $default;
            $profit = $amount / 100 * $percent;

            $referrer->increment('referral_balance', $profit);
            ReferralProfit::create(['from_id' => $user->id, 'ref_id' => $referrer->id, 'amount' => $profit, 'level' => $level]);

            $current = $referrer;
        }
    }
}
