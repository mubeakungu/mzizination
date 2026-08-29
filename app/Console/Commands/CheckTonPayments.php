<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TonPayment;
use App\User;
use App\Transaction;
use App\ReferralProfit;
use App\Services\TonService;
use Carbon\Carbon;
use DB;
use Log;

class CheckTonPayments extends Command
{
    protected $signature = 'ton:check';
    protected $description = 'Check TON wallet transactions and credit user balances by MEMO';

    protected $tonService;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->tonService = new TonService();
        
        $this->info('[' . date('Y-m-d H:i:s') . '] Checking TON transactions...');

        $transactions = $this->tonService->getTransactions(50);
        
        if (empty($transactions)) {
            $this->info('No transactions found.');
            return 0;
        }

        $this->info("Fetched " . count($transactions) . " transactions.");
        
        $credited = 0;
        $minTon = config('ton.min_amount_rub', 12) / $this->tonService->getRate();

        foreach ($transactions as $tx) {
            // Проверяем формат MEMO: USER_<id>
            $memo = strtoupper(trim($tx['memo']));
            if (!preg_match('/^USER_(\d+)$/', $memo, $matches)) {
                continue;
            }

            $userId = (int) $matches[1];
            
            // Проверяем минимальную сумму
            if ($tx['amount'] < $minTon * 0.95) {
                continue;
            }

            // Проверяем, не обработана ли уже эта транзакция
            $exists = TonPayment::where('tx_hash', $tx['hash'])->exists();
            if ($exists) {
                continue;
            }

            // Проверяем существование пользователя
            $user = User::find($userId);
            if (!$user) {
                $this->warn("User #{$userId} not found for TX: {$tx['hash']}");
                continue;
            }

            // Зачисляем платеж
            if ($this->creditPayment($user, $tx, $memo)) {
                $credited++;
                $this->info("✓ Credited {$tx['amount']} TON to user #{$userId}");
            }
        }

        $this->info("Total credited: {$credited} payments.");
        return 0;
    }

    private function creditPayment(User $user, array $tx, string $memo): bool
    {
        DB::beginTransaction();

        try {
            $amountRub = round($this->tonService->tonToRub($tx['amount']), 2);

            // Создаем запись платежа
            TonPayment::create([
                'user_id' => $user->id,
                'memo' => $memo,
                'amount_ton' => $tx['amount'],
                'amount_rub' => $amountRub,
                'rate' => $this->tonService->getRate(),
                'status' => TonPayment::STATUS_CONFIRMED,
                'tx_hash' => $tx['hash'],
                'expires_at' => Carbon::now()
            ]);

            // Зачисляем баланс
            $user->increment('balance', $amountRub);
            $user->increment('wager', $amountRub * 2);

            // Реферальные
            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $amountRub);
            }

            // Транзакция в историю
            Transaction::create([
                'user_id' => $user->id,
                'action' => 'Пополнение TON',
                'amount' => $amountRub,
                'type' => 'up'
            ]);

            DB::commit();

            Log::info("TON Payment credited", [
                'user_id' => $user->id,
                'amount_ton' => $tx['amount'],
                'amount_rub' => $amountRub,
                'tx_hash' => $tx['hash']
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("TON credit error: " . $e->getMessage());
            $this->error("Error: " . $e->getMessage());
            return false;
        }
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
}
