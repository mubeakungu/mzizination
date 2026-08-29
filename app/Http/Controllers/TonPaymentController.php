<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\TonPayment;
use App\User;
use App\Transaction;
use App\ReferralProfit;
use App\Services\TonService;
use Carbon\Carbon;
use DB;

class TonPaymentController extends Controller
{
    protected $tonService;

    public function __construct()
    {
        parent::__construct();
        $this->tonService = new TonService();
    }

    /**
     * Получить информацию для оплаты
     */
    public function getInfo()
    {
        $rate = $this->tonService->getRate();
        $minRub = config('ton.min_amount_rub', 12);
        $minTon = $this->tonService->rubToTon($minRub);

        // Генерируем уникальный MEMO для пользователя (детерминированный на основе user_id)
        $secret = config('app.key', 'golden1x_secret');
        $hash = strtoupper(substr(md5($this->user->id . $secret), 0, 6));
        $memo = 'TON' . $this->user->id . $hash;

        return [
            'rate' => $rate,
            'min_rub' => $minRub,
            'min_ton' => round($minTon, 4),
            'wallet' => $this->tonService->getDepositWallet(),
            'memo' => $memo
        ];
    }

    /**
     * Получить актуальный курс TON (принудительное обновление)
     */
    public function getRate()
    {
        $rate = $this->tonService->getFreshRate();
        $minRub = config('ton.min_amount_rub', 12);
        $minTon = $minRub / $rate;

        return [
            'rate' => $rate,
            'min_rub' => $minRub,
            'min_ton' => round($minTon, 4)
        ];
    }

    /**
     * Создать платеж
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return ['error' => true, 'message' => $validator->errors()->first()];
        }

        $amountRub = (float) $request->amount;
        $minRub = config('ton.min_amount_rub', 12);

        if ($amountRub < $minRub) {
            return ['error' => true, 'message' => "Минимальная сумма {$minRub} ₽"];
        }

        // Проверяем активный платеж
        $existingPayment = TonPayment::where('user_id', $this->user->id)
            ->where('status', TonPayment::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingPayment) {
            return $this->formatPaymentResponse($existingPayment);
        }

        $rate = $this->tonService->getRate();
        $amountTon = $this->tonService->rubToTon($amountRub);
        $memo = 'PAY_' . $this->user->id . '_' . strtoupper(substr(md5(time()), 0, 6));

        $payment = TonPayment::create([
            'user_id' => $this->user->id,
            'memo' => $memo,
            'amount_ton' => $amountTon,
            'amount_rub' => $amountRub,
            'rate' => $rate,
            'expires_at' => Carbon::now()->addMinutes(30)
        ]);

        return $this->formatPaymentResponse($payment);
    }

    /**
     * Проверить статус платежа
     */
    public function checkStatus(Request $request)
    {
        $payment = TonPayment::where('id', $request->payment_id)
            ->where('user_id', $this->user->id)
            ->first();

        if (!$payment) {
            return ['error' => true, 'message' => 'Платеж не найден'];
        }

        if ($payment->isPending() && !$payment->isExpired()) {
            $this->checkPaymentTransaction($payment);
            $payment->refresh();
        }

        return [
            'status' => $payment->status,
            'is_expired' => $payment->isExpired(),
            'balance' => $this->user->balance
        ];
    }

    /**
     * Проверить транзакцию
     */
    private function checkPaymentTransaction(TonPayment $payment)
    {
        $transactions = $this->tonService->getTransactions(30);

        foreach ($transactions as $tx) {
            if (strtoupper(trim($tx['memo'])) !== strtoupper($payment->memo)) {
                continue;
            }
            if ($tx['amount'] < $payment->amount_ton * 0.99) {
                continue;
            }
            if ($tx['timestamp'] < $payment->created_at->timestamp - 60) {
                continue;
            }

            $exists = TonPayment::where('tx_hash', $tx['hash'])->exists();
            if ($exists) continue;

            $this->confirmPayment($payment, $tx);
            return true;
        }
        return false;
    }

    /**
     * Подтвердить платеж
     */
    private function confirmPayment(TonPayment $payment, array $tx)
    {
        DB::beginTransaction();
        try {
            $actualRub = round($this->tonService->tonToRub($tx['amount']), 2);

            $payment->update([
                'status' => TonPayment::STATUS_CONFIRMED,
                'tx_hash' => $tx['hash'],
                'amount_ton' => $tx['amount'],
                'amount_rub' => $actualRub
            ]);

            $user = User::find($payment->user_id);
            $user->increment('balance', $actualRub);
            $user->increment('wager', $actualRub * 2);

            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $actualRub);
            }

            Transaction::create([
                'user_id' => $payment->user_id,
                'action' => 'Пополнение TON',
                'amount' => $actualRub,
                'type' => 'up'
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('TON confirm error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * История платежей
     */
    public function history()
    {
        $payments = TonPayment::where('user_id', $this->user->id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        return [
            'payments' => $payments->map(function ($p) {
                return [
                    'id' => $p->id,
                    'amount_ton' => $p->amount_ton,
                    'amount_rub' => $p->amount_rub,
                    'status' => $p->status,
                    'created_at' => $p->created_at->toIso8601String()
                ];
            })
        ];
    }

    /**
     * Отменить платеж
     */
    public function cancel(Request $request)
    {
        $payment = TonPayment::where('id', $request->payment_id)
            ->where('user_id', $this->user->id)
            ->where('status', TonPayment::STATUS_PENDING)
            ->first();

        if ($payment) {
            $payment->update(['status' => TonPayment::STATUS_EXPIRED]);
        }

        return ['success' => true];
    }

    /**
     * Проверить платежи по MEMO пользователя
     */
    public function checkMemo()
    {
        // Генерируем MEMO пользователя (тот же алгоритм что в getInfo)
        $secret = config('app.key', 'golden1x_secret');
        $hash = strtoupper(substr(md5($this->user->id . $secret), 0, 6));
        $userMemo = 'TON' . $this->user->id . $hash;
        
        $minTon = config('ton.min_amount_rub', 12) / $this->tonService->getRate();
        
        $transactions = $this->tonService->getTransactions(50);
        $credited = 0;

        foreach ($transactions as $tx) {
            $txMemo = strtoupper(trim($tx['memo']));
            if ($txMemo !== strtoupper($userMemo)) {
                continue;
            }

            if ($tx['amount'] < $minTon * 0.95) {
                continue;
            }

            // Проверяем, не обработана ли уже эта транзакция
            $exists = TonPayment::where('tx_hash', $tx['hash'])->exists();
            if ($exists) {
                continue;
            }

            // Создаем и подтверждаем платеж
            $amountRub = round($this->tonService->tonToRub($tx['amount']), 2);
            
            DB::beginTransaction();
            try {
                $payment = TonPayment::create([
                    'user_id' => $this->user->id,
                    'memo' => $userMemo,
                    'amount_ton' => $tx['amount'],
                    'amount_rub' => $amountRub,
                    'rate' => $this->tonService->getRate(),
                    'status' => TonPayment::STATUS_CONFIRMED,
                    'tx_hash' => $tx['hash'],
                    'expires_at' => Carbon::now()
                ]);

                $this->user->increment('balance', $amountRub);
                $this->user->increment('wager', $amountRub * 2);

                if (!is_null($this->user->referral_use)) {
                    $this->setReferralProfit($this->user->id, $amountRub);
                }

                Transaction::create([
                    'user_id' => $this->user->id,
                    'action' => 'Пополнение TON',
                    'amount' => $amountRub,
                    'type' => 'up'
                ]);

                DB::commit();
                $credited += $amountRub;
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('TON checkMemo error: ' . $e->getMessage());
            }
        }

        return [
            'credited' => $credited,
            'balance' => $this->user->fresh()->balance
        ];
    }

    /**
     * Проверить все ожидающие платежи (для cron)
     */
    public function checkAll()
    {
        $pending = TonPayment::where('status', TonPayment::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->get();

        $confirmed = 0;
        foreach ($pending as $payment) {
            if ($this->checkPaymentTransaction($payment)) {
                $confirmed++;
            }
        }

        return ['confirmed' => $confirmed];
    }

    private function setReferralProfit($userId, $amount)
    {
        $user = User::find($userId);
        $amount = $amount / 100;

        $referral1 = User::find($user->referral_use);
        if ($referral1) {
            $percent = $referral1->ref_1_lvl > 0 ? $referral1->ref_1_lvl : 10;
            $referral1->increment('referral_balance', $amount * $percent);
            ReferralProfit::create([
                'from_id' => $user->id,
                'ref_id' => $referral1->id,
                'amount' => $amount * $percent,
                'level' => 1
            ]);
        }
    }

    private function formatPaymentResponse(TonPayment $payment)
    {
        return [
            'payment_id' => $payment->id,
            'wallet' => $this->tonService->getDepositWallet(),
            'memo' => $payment->memo,
            'amount_ton' => round($payment->amount_ton, 4),
            'amount_rub' => $payment->amount_rub,
            'rate' => $payment->rate,
            'expires_at' => $payment->expires_at->toIso8601String()
        ];
    }
}
