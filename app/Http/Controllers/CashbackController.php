<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\RankService;

use App\User;
use App\Payment;
use App\Withdraw;
use Carbon\Carbon;
use DB;

class CashbackController extends Controller
{
    public function send()
    {
        $payments = Payment::groupBy('user_id')
            ->where('status', 1)
            ->where('fake', 0)
            ->where('created_at', '>=', Carbon::today()->subDays(7))
            ->select('user_id', DB::raw('SUM(sum) as deposits'))
            ->get();

        $withdraws = Withdraw::groupBy('user_id')
            ->where('status', [0, 1])
            ->where('fake', 0)
            ->where('created_at', '>=', Carbon::today()->subDays(7))
            ->select('user_id', DB::raw('ifnull(SUM(sum), 0) as withdraws'))
            ->get();

        DB::transaction(function () use ($payments, $withdraws) {
            foreach($payments as $payment)
            {
                $user = User::find($payment->user_id);
    
                // узнаем кол-во депозитов и выплат
                $depositsSum = $payment->deposits;
                $withdrawsSum = $withdraws->isEmpty()
                    ? 0
                    : $withdraws->where('user_id', $user->id)->first()->withdraws ?? 0;
    
                // узнаем % кешбека пользователя
                $rank = new RankService($user);
                $cashback = $rank->get()['cashback'];
                
                // считаем кешбек пользователя
                $calc = ($depositsSum - $withdrawsSum - $user->balance) / 100 * $cashback;
                
                $user->cashback_balance = $calc;
                $user->save();
            }
        });

        return 'OK';
    }

    public function reset()
    {
        User::query()->update(['cashback_balance' => 0]);
        return 'OK';
    }
}