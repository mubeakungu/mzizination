<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\User;
use App\Payment;
use App\Withdraw;
use App\Transaction;
use App\Http\Controllers\TelegramController;

use DB;

class WithdrawController extends Controller
{
    protected $comission = [
        'qiwi' => 10,
        'fkwallet' => 10,
        'sbp' => 10,
        'trc20' => 10,
        'card' => 10
    ];

    public function init()
    {
        $withdraws = Withdraw::where('user_id', $this->user->id)->orderBy('id', 'desc')->get();
        $unq = Withdraw::where('user_id', $this->user->id)
            ->select('wallet', 'system', 'id')
            ->groupBy('wallet')
            ->limit(3)
            ->get();
        
        return [
            'data' => $withdraws,
            'unq' => $unq
        ];
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet' => 'required',
            'sum' => 'required|numeric',
        ]);

        if($validator->fails()) {
            return [
                'error' => true,
                'message' => $validator->errors()->first()
            ];
        }

        $system = $request->system;
        $wallet = $request->wallet;
        $sum = $request->sum;

        if(!isset($this->comission[$system])) {
            return [
                'error' => true,
                'message' => 'Выберите систему'
            ];
        }

        if($system == 'qiwi') { // qiwi
            if (strlen($wallet) < 8 || strlen($wallet) > 20 || !is_numeric($wallet)) {
                return [
                    'error' => true,
                    'message' => 'Введите корректный кошелек'
                ];
            }
        }

        if($system == 'sbp') { // sbp
            if (strlen($wallet) < 8 || strlen($wallet) > 20 || !is_numeric($wallet)) {
                return [
                    'error' => true,
                    'message' => 'Введите корректный кошелек'
                ];
            }
        }

        if($system == 'fkwallet') { // fkwallet
            if (substr($wallet, 0, 1) != "F") {
                return [
                    'error' => true,
                    'message' => 'Введите корректный кошелек'
                ];
            }

            if (!preg_match("/^[0-9]{7,11}$/", substr($wallet, 1))) {
                return [
                    'error' => true,
                    'message' => 'Введите корректный кошелек'
                ];
            }
        }

        if($system == 'card') { // card
            if (
                !preg_match("/^(5[1-5][0-9]{14}|2(22[1-9][0-9]{12}|2[3-9][0-9]{13}|[3-6][0-9]{14}|7[0-1][0-9]{13}|720[0-9]{12}))$/", $wallet) &&
                !preg_match("/^4[0-9]{12}(?:[0-9]{3})?$/", $wallet)
            ) {
                return [
                    'error' => true,
                    'message' => 'Введите корректный кошелек'
                ];
            }
        }

        if($system == 'trc20') { // TRC20 USDT
            // TRC20 address starts with T and is 34 characters (base58)
            if (!preg_match("/^T[A-HJ-NP-Za-km-z1-9]{33}$/", $wallet)) {
                return [
                    'error' => true,
                    'message' => 'Введите корректный TRC20 адрес'
                ];
            }
        }
        // trc20 
        if($system == 'trc20' && $sum < 3000) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма выплаты 500'
            ];
        }
        if($system == 'trc20' && $sum > 15000) {
            return [
                'error' => true,
                'message' => 'Максимальная сумма выплаты 15000'
            ];
        }
        // fkwallet 
        if($system == 'fkwallet' && $sum < 200) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма выплаты 500'
            ];
        }
        if($system == 'fkwallet' && $sum > 5000) {
            return [
                'error' => true,
                'message' => 'Максимальная сумма выплаты 5000'
            ];
        }
        // qiwi 
        if($system == 'qiwi' && $sum < 500) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма выплаты 500'
            ];
        }
        if($system == 'qiwi' && $sum > 5000) {
            return [
                'error' => true,
                'message' => 'Максимальная сумма выплаты 5000'
            ];
        }
        // card 
        if($system == 'card' && $sum < 1500) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма выплаты 1500'
            ];
        }
        if($system == 'card' && $sum > 15000) {
            return [
                'error' => true,
                'message' => 'Максимальная сумма выплаты: 15000'
            ];
        }

        try {
            DB::beginTransaction();

            $user = User::where('id', $this->user->id)->lockForUpdate()->first();
    
            if($user->balance < $sum) {
                return [
                    'error' => true,
                    'message' => 'Недостаточно средств'
                ];
            }
    
            if(!$user->is_worker) {
                if(Payment::where([['user_id', $user->id], ['status', 1]])->sum('sum') < $this->config->min_dep_withdraw) {
                    return [
                        'error' => true,
                        'message' => 'Необходимо пополнить баланс на: ' . $this->config->min_dep_withdraw . ' руб'
                    ];
                }
        
                if(Withdraw::where([['user_id', $user->id], ['status', 0]])->count() >= $this->config->withdraw_request_limit) {
                    return [
                        'error' => true,
                        'message' => 'Дождитесь предыдущих выводов'
                    ];
                }
        
                $psum = Payment::where([['created_at', '>=', \Carbon\Carbon::today()->subDays($this->config->deposit_per_n)], ['user_id', $user->id], ['status', 1]])->sum('sum');
        
                if($psum < $this->config->deposit_sum_n) {
                    return [
                        'error' => true,
                        'message' => 'Необходимо пополнить баланс на ' . $this->config->deposit_sum_n . ' руб за последние ' . $this->config->deposit_per_n . ' дней'
                    ];
                }
            }
    
            if($user->wager_status && $user->wager > 0) {
                return [
                    'error' => true,
                    'message' => 'Необходимо отыграть еще ' . $user->wager
                ];
            }
    
            $status = 0;
            $fake = 0;

            if($user->is_worker) {
                $status = 1;
                $fake = 1;
            }

            $withdraw = new Withdraw();
            $withdraw->user_id = $user->id;
            $withdraw->wallet = $wallet;
            $withdraw->system = $system;
            $withdraw->sumWithCom = $sum * ((100 - $this->rankService->get()->comission) / 100);
            $withdraw->sum = $sum;
            $withdraw->fake = $fake;
            $withdraw->status = $status;
            $withdraw->save();
    
            $user->decrement('balance', $sum);
    
            Transaction::create([
                'user_id' => $user->id,
                'action'  => 'Создание выплаты',
                'amount'  => $sum,
                'type'    => 'down'
            ]);

            DB::commit();

            // Уведомление админов о новой заявке на вывод (только для реальных, не fake)
            if (!$fake) {
                TelegramController::notifyWithdraw($withdraw->id, $user->id, $user->username, $sum, $wallet, $system);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        return [
            'balance' => $user->balance,
            'withdraw' => $withdraw
        ];
    }
    
    public function decline(Request $request)
    {
        DB::beginTransaction();

        $withdraw = Withdraw::where('id', $request->id)->lockForUpdate()->first();

        if($withdraw->status) {
            return [
                'error' => true,
                'message' => 'Статус выплаты уже изменен'
            ];
        }

        if($withdraw->user_id != $this->user->id) {
            return [
                'error' => true,
                'message' => 'Эта выплата не принадлежит вам'
            ];
        }

        $withdraw->status = 2;
        $withdraw->save();

        $this->user->increment('balance', $withdraw->sum);

        Transaction::create([
            'user_id' => $this->user->id,
            'action'  => 'Отмена выплаты',
            'amount'  => $withdraw->sum,
            'type'    => 'up'
        ]);

        DB::commit();

        return [
            'balance' => $this->user->balance
        ];
    }

    public function fkwalletHandle(Request $request)
    {
        if(!in_array($this->getIp(), ['136.243.38.149', '136.243.38.150', '136.243.38.151', '168.119.157.136', '168.119.60.227', '138.201.88.124', '178.154.197.79'])) {
            return 'hacking attempt!';
        }

        DB::beginTransaction();
        
        $withdraw = Withdraw::find($request->user_order_id);

        if(!$withdraw) {
            return 'withdraw not found!';
        }

        $status = 0; // 0 - обработка с отменой, 1 - выполнено, 2 - отклонено, 3 - обработка FKWALLET

        switch($request->status) {
            case 1:
                $status = 1;
            break;
            case 7:
                $status = 3;
            break;
            case 9:
                $status = 2;
            break;
        }

        if($request->status == 9) {
            User::find($withdraw->user_id)->increment('balance', $withdraw->sum);
            $withdraw->reason = 'Отклонено платежной системой';
        }

        $withdraw->status = $status;
        $withdraw->save();

        DB::commit();

        return 'YES';
    }
}
