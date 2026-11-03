<?php

namespace App\Http\Controllers;

use App\Payment;
use App\Services\DepositService;
use App\Services\MpesaService;
use App\Transaction;
use App\User;
use App\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * M-Pesa deposits (STK push) and the Daraja callbacks for deposits and B2C withdrawals.
 *
 * Payment status: 0 waiting for the customer, 1 paid, 2 failed or cancelled.
 * Withdraw status: 0 waiting for admin, 3 sent to M-Pesa, 1 paid, 2 declined (refunded).
 */
class MpesaController extends Controller
{
    private const ACK = ['ResultCode' => 0, 'ResultDesc' => 'Accepted'];

    public function __construct(private MpesaService $mpesa)
    {
        parent::__construct();
    }

    public function deposit(Request $request)
    {
        if (!$this->mpesa->isConfigured()) {
            return ['error' => true, 'message' => 'M-Pesa deposits are not available yet'];
        }

        $min = config('mpesa.min_deposit');
        $max = config('mpesa.max_deposit');
        $amount = $request->amount;

        if (!is_numeric($amount) || (int) $amount != $amount) {
            return ['error' => true, 'message' => 'Enter a whole number of shillings'];
        }
        $amount = (int) $amount;
        if ($amount < $min || $amount > $max) {
            return ['error' => true, 'message' => "Deposits are between KES {$min} and KES " . number_format($max)];
        }
        if (!MpesaService::validatePhoneNumber($request->phone)) {
            return ['error' => true, 'message' => 'Enter a Safaricom number, e.g. 0712 345 678'];
        }
        $phone = MpesaService::formatPhoneNumber($request->phone);

        // One prompt at a time: a second push while the first is on the phone fails anyway.
        $pending = Payment::where('user_id', $this->user->id)->where('method', 'mpesa')->where('status', 0)
            ->where('created_at', '>', now()->subSeconds(90))->exists();
        if ($pending) {
            return ['error' => true, 'message' => 'Finish the M-Pesa prompt on your phone first, or wait a minute and try again'];
        }

        $payment = Payment::create([
            'user_id' => $this->user->id,
            'sum' => $amount,
            'method' => 'mpesa',
            'bonus' => 0,
            'phone' => $phone,
            'status' => 0,
        ]);

        $push = $this->mpesa->stkPush($phone, $amount, (string) $payment->id);

        if (!$push['success']) {
            $payment->update(['status' => 2, 'result_desc' => $push['message']]);
            return ['error' => true, 'message' => 'M-Pesa could not send the prompt. Please try again.'];
        }

        $payment->update(['checkout_request_id' => $push['checkout_request_id']]);

        return ['success' => true, 'id' => $payment->id, 'message' => 'Check your phone and enter your M-Pesa PIN'];
    }

    /** Polled by the cashier while the customer is on the PIN prompt. */
    public function depositStatus(Request $request)
    {
        $payment = Payment::where('id', (int) $request->id)->where('user_id', $this->user->id)->where('method', 'mpesa')->first();
        if (!$payment) return ['error' => true, 'message' => 'Deposit not found'];

        // If the callback is late or lost, ask Safaricom directly (not more than every 15s).
        if ((int) $payment->status === 0 && $payment->checkout_request_id
            && $payment->created_at->lt(now()->subSeconds(20))
            && cache()->add('mpesa_q_' . $payment->id, 1, 15)) {
            $this->settle($payment, null);
            $payment->refresh();
        }

        return [
            'status' => (int) $payment->status,
            'message' => $payment->result_desc,
            'balance' => (float) User::find($this->user->id)->balance,
        ];
    }

    public function stkCallback(Request $request, string $secret)
    {
        if (!$this->secretOk($secret)) abort(404);

        $cb = $request->input('Body.stkCallback', []);
        Log::info('M-Pesa STK callback', ['callback' => $cb]);

        $payment = !empty($cb['CheckoutRequestID'])
            ? Payment::where('checkout_request_id', $cb['CheckoutRequestID'])->first()
            : null;

        if ($payment && (int) $payment->status === 0) {
            $this->settle($payment, $cb);
        }

        return response()->json(self::ACK);
    }

    /**
     * Decide a pending deposit. A callback alone is never trusted: Daraja callbacks
     * aren't signed, so a success is only credited after Safaricom's STK query agrees.
     */
    private function settle(Payment $payment, ?array $cb): void
    {
        $meta = MpesaService::items($cb['CallbackMetadata']['Item'] ?? null);

        if ($cb !== null && (string) ($cb['ResultCode'] ?? '') !== '0') {
            // The customer cancelled, timed out or had too little money. Confirm before closing,
            // so a forged failure can't cancel a deposit that actually went through.
            $q = $this->mpesa->stkQuery($payment->checkout_request_id);
            if ($q['state'] === 'paid') {
                $this->credit($payment, $meta);
            } elseif ($q['state'] === 'failed') {
                $this->fail($payment, $cb['ResultDesc'] ?? $q['desc']);
            }
            return;
        }

        $q = $this->mpesa->stkQuery($payment->checkout_request_id);
        if ($q['state'] === 'paid') {
            $this->credit($payment, $meta);
        } elseif ($q['state'] === 'failed') {
            $this->fail($payment, $q['desc']);
        } elseif ($cb === null && $payment->created_at->lt(now()->subMinutes(10))) {
            // Nothing from Safaricom after 10 minutes: the prompt has long expired.
            $this->fail($payment, 'No response from M-Pesa');
        }
    }

    private function credit(Payment $payment, array $meta): void
    {
        // The STK query confirms success but not the amount; the callback amount, when we
        // have it, must cover the order. Without it the push itself fixed the amount.
        $paid = isset($meta['Amount']) ? (float) $meta['Amount'] : (float) $payment->sum;

        $error = DepositService::complete($payment->id, $paid, array_filter([
            'mpesa_receipt' => $meta['MpesaReceiptNumber'] ?? null,
            'result_desc' => 'Paid',
        ]));

        if ($error && $error !== 'payment already paid') {
            Log::error('M-Pesa deposit not credited', ['payment' => $payment->id, 'error' => $error, 'meta' => $meta]);
        }
    }

    private function fail(Payment $payment, ?string $desc): void
    {
        Payment::where('id', $payment->id)->where('status', 0)
            ->update(['status' => 2, 'result_desc' => substr((string) $desc, 0, 250) ?: 'Failed']);
    }

    public function b2cResult(Request $request, string $secret)
    {
        if (!$this->secretOk($secret)) abort(404);

        $result = $request->input('Result', []);
        Log::info('M-Pesa B2C result', ['result' => $result]);

        $withdraw = $this->findWithdraw($result);
        if (!$withdraw) return response()->json(self::ACK);

        DB::transaction(function () use ($withdraw, $result) {
            $w = Withdraw::where('id', $withdraw->id)->lockForUpdate()->first();
            if ((int) $w->status !== 3) return; // already settled

            if ((string) ($result['ResultCode'] ?? '') === '0') {
                $params = MpesaService::items($result['ResultParameters']['ResultParameter'] ?? null, 'Key');
                $w->update([
                    'status' => 1,
                    'mpesa_receipt' => $result['TransactionID'] ?? ($params['TransactionReceipt'] ?? null),
                    'result_desc' => 'Paid',
                ]);
            } else {
                $this->refund($w, $result['ResultDesc'] ?? 'M-Pesa payment failed');
            }
        });

        return response()->json(self::ACK);
    }

    /**
     * Safaricom queued the request too long. The money may or may not have moved, so
     * the withdrawal stays "sent" for an admin to check against the M-Pesa statement.
     */
    public function b2cTimeout(Request $request, string $secret)
    {
        if (!$this->secretOk($secret)) abort(404);

        $result = $request->input('Result', []);
        Log::warning('M-Pesa B2C timeout', ['result' => $result]);

        if ($withdraw = $this->findWithdraw($result)) {
            $withdraw->update(['result_desc' => 'M-Pesa timed out. Check the M-Pesa statement before paying again.']);
        }

        return response()->json(self::ACK);
    }

    private function findWithdraw(array $result): ?Withdraw
    {
        if (!empty($result['OriginatorConversationID'])) {
            $w = Withdraw::where('originator_conversation_id', $result['OriginatorConversationID'])->first();
            if ($w) return $w;
        }
        return !empty($result['ConversationID'])
            ? Withdraw::where('conversation_id', $result['ConversationID'])->first()
            : null;
    }

    private function refund(Withdraw $w, string $reason): void
    {
        $user = User::where('id', $w->user_id)->lockForUpdate()->first();
        $user->increment('balance', $w->sum);
        $w->update(['status' => 2, 'reason' => substr($reason, 0, 250), 'result_desc' => substr($reason, 0, 250)]);

        Transaction::create([
            'user_id' => $user->id,
            'action' => 'Withdrawal refunded',
            'amount' => $w->sum,
            'type' => 'up',
        ]);
    }

    private function secretOk(string $secret): bool
    {
        $expected = (string) config('mpesa.callback_secret');
        return $expected !== '' && hash_equals($expected, $secret);
    }

    /** Sends an approved withdrawal to the customer's phone. Used by the admin panel. */
    public static function sendWithdrawal(Withdraw $withdraw): array
    {
        $mpesa = app(MpesaService::class);
        if (!$mpesa->b2cConfigured()) {
            return ['error' => true, 'message' => 'M-Pesa B2C is not configured (MPESA_INITIATOR_NAME, MPESA_INITIATOR_PASSWORD, certificate)'];
        }

        // Claim the withdrawal atomically so a double click can't pay it twice.
        $claimed = Withdraw::where('id', $withdraw->id)->where('status', 0)
            ->update(['status' => 3, 'result_desc' => 'Sending to M-Pesa']);
        if (!$claimed) {
            return ['error' => true, 'message' => 'This withdrawal was already handled', 'reload' => true];
        }

        $amount = (int) floor($withdraw->sumWithCom ?: $withdraw->sum);
        $r = $mpesa->b2c(MpesaService::formatPhoneNumber($withdraw->wallet), $amount, 'Withdrawal ' . $withdraw->id);

        if (!$r['success']) {
            Withdraw::where('id', $withdraw->id)->update(['status' => 0, 'result_desc' => substr('M-Pesa: ' . $r['message'], 0, 250)]);
            return ['error' => true, 'message' => 'M-Pesa: ' . $r['message']];
        }

        $withdraw->refresh()->update([
            'conversation_id' => $r['conversation_id'],
            'originator_conversation_id' => $r['originator_conversation_id'],
            'result_desc' => 'Sent to M-Pesa',
        ]);

        return ['message' => 'Sent to M-Pesa. It is marked paid when Safaricom confirms.', 'status' => 3];
    }
}
