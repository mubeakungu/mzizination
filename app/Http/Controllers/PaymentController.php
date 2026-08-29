<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Payment;
use App\User;
use App\Setting;
use App\ReferralProfit;
use App\Promocode;
use App\PromocodeActivation;
use App\Transaction;
use App\Http\Controllers\TelegramController;

use DB;

class PaymentController extends Controller
{
    public function init()
    {
        $data = Payment::where('user_id', $this->user->id)->orderBy('id', 'desc')->get();
        return ['payments' => $data];
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
        ]);

        if($validator->fails()) {
            return [
                'error' => true,
                'message' => $validator->errors()->first()
            ];
        }

        switch($request->system) {
            case 'fk':
                $payment = (object) $this->createOrder($request);

                if($payment->error) {
                    return [
                        'error' => true,
                        'message' => $payment->message
                    ];
                }

                $merchantId = env('FREEKASSA_MERCHANT_ID', $this->config->kassa_id);
                $secret1 = env('FREEKASSA_SECRET_1', $this->config->kassa_secret1);
                
                $sign = md5($merchantId.':'.$payment->sum.':'.$secret1.':RUB:'.$payment->id);
        
                $data = [
                    'm'        => $merchantId,
                    'oa'       => $payment->sum,
                    'o'        => $payment->id,
                    'currency' => 'RUB',
                    's'        => $sign
                ];
        
                $url = "https://pay.freekassa.ru/?".http_build_query($data);
            break;
            case 'sbp':
                $payment = (object) $this->createOrder($request);

                if($payment->error) {
                    return [
                        'error' => true,
                        'message' => $payment->message
                    ];
                }

                $rubpayProjectId = env('RUBPAY_PROJECT_ID', 1187);
                $rubpaySecret = env('RUBPAY_SECRET', '16b5cadcc140d5fcd5d5bcaec59ef8c1');
                
                $data = [
                    'project_id'     => $rubpayProjectId,
                    'amount'         => $payment->sum,
                    'order_id'       => $payment->id,
                    'sign'           => md5($rubpaySecret . $rubpayProjectId . $payment->id . $payment->sum . "1" . $rubpaySecret),
                    'payment_method' => 7
                ];
        
                $url = "https://rubpay.io/pay/create?".http_build_query($data);
            break;
            case 'qiwi':
                $payment = (object) $this->createOrder($request);

                if($payment->error) {
                    return [
                        'error' => true,
                        'message' => $payment->message
                    ];
                }

                $rubpayProjectId = env('RUBPAY_PROJECT_ID', 1187);
                $rubpaySecret = env('RUBPAY_SECRET', '16b5cadcc140d5fcd5d5bcaec59ef8c1');

                $data = [
                    'project_id'     => $rubpayProjectId,
                    'amount'         => $payment->sum,
                    'order_id'       => $payment->id,
                    'sign'           => md5($rubpaySecret . $rubpayProjectId . $payment->id . $payment->sum . "1" . $rubpaySecret),
                    'payment_method' => 1
                ];
        
                $url = "https://rubpay.io/pay/create?".http_build_query($data);
            break;
            case 'card':
                $payment = (object) $this->createOrder($request);

                if($payment->error) {
                    return [
                        'error' => true,
                        'message' => $payment->message
                    ];
                }

                $rukassaShopId = env('RUKASSA_SHOP_ID', 1049);
                $rukassaToken = env('RUKASSA_TOKEN', '8cf310cb702a9ee642e626be9f5243a0');
                
                $data = [
                    'shop_id'       => $rukassaShopId,
                    'token'         => $rukassaToken,
                    'order_id'      => $payment->id,
                    'amount'        => $payment->sum,
                    'method'        => 'sbp',
                ];

                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, "https://lk.rukassa.pro/api/v1/create");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                $response = curl_exec($curl);
                curl_close($curl);
                $result = json_decode($response, true);

                  curl_setopt($curl, CURLOPT_URL, $result['url'].'&json');
                  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                  curl_setopt($curl, CURLOPT_POST, true);
                  curl_setopt($curl, CURLOPT_ENCODING, true);

                  $response = curl_exec($curl);
                  curl_close($curl);

                  $result = json_decode($response, true);
                dd($result);
            break;
            default:
                return [
                    'error' => true,
                    'message' => 'Выберите способ оплаты'
                ];
            break;
        }

        return [
            'url' => $url
        ];
    }


    public function rubpay(Request $request)
    {
        if(isset($request->status) && $request->status == 2) {
            $rubpayProjectId = env('RUBPAY_PROJECT_ID', 1187);
            $rubpaySecret = env('RUBPAY_SECRET', '16b5cadcc140d5fcd5d5bcaec59ef8c1');
            
            $hash = md5($rubpayProjectId . $request->order_id . $request->payment_id . $request->amount . $request->currency . $request->status . $rubpaySecret);
            if($hash != $request->hash) return 'wrong sign';

            $this->setPayment($request->order_id, $request->amount);

            return 'YES';
        } else {
            return 'error';
        }
    }
    
    public function fk(Request $request)
    {
        $merchantId = env('FREEKASSA_MERCHANT_ID', $this->config->kassa_id);
        $secret2 = env('FREEKASSA_SECRET_2', $this->config->kassa_secret2);
        
        $sign = md5($merchantId.':'.$request->AMOUNT.':'.$secret2.':'.$request->MERCHANT_ORDER_ID);
        if ($sign != $request->SIGN) return 'wrong sign';

        $this->setPayment($request->MERCHANT_ORDER_ID, $request->AMOUNT);

        return 'YES';
    }

    private function createOrder($request, $bonus = 0)
    {
        // Custom minimums per system
        $min_sums = [
            'fk' => 100,
            'sbp' => 300,
            'qiwi' => 100,
            'card' => 500,
            'trc20' => 3000
        ];

        $system = $request->system ?? 'fk';
        $min_sum = $min_sums[$system] ?? 100;

        if($request->amount < $min_sum) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения ' . $min_sum . ' руб'
            ];
        }
        
        $code = $request->code;

        if(date('D') == 'Sun' && $request->amount >= 150) {
            $bonus += 10;
        }

        if(isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }
    
            if($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            if($request->amount > 1000) {
                return [
                    'error' => true,
                    'message' => 'Максимальная сумма пополнения при использовании промокода - 1000р'
                ];
            }
    
            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');
    
            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }
    
            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();
    
            if ($used) {
                return [
                    'error' => true,
                    'message' => 'Вы уже использовали этот код'
                ];
            }

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
        }

        $payment = Payment::create([
            'user_id' => $this->user->id,
            'sum' => $request->amount,
            'method' => $request->method,
            'bonus' => $bonus
        ]);

        return $payment;
    }
    private function setPayment($order_id, $order_sum) {
        $payment = Payment::find($order_id);

        if(!$payment) {
            return 'payment not found';
        }

        if($payment->status) {
            return 'payment already paid';
        }

        if($payment->sum > $order_sum) {
            return 'wrong sum';
        }

        $incrementSum = $payment->bonus != 0
            ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
            : $payment->sum;

        $user = User::find($payment->user_id);
        $user->increment('balance', $incrementSum);
        $user->increment('wager', $incrementSum * 2);
    
        if(!is_null($user->referral_use)) {
            $this->setReferralProfit($user->id, $payment->sum);
        }

        $payment->status = 1;
        $payment->save();

        Transaction::create([
            'user_id' => $user->id,
            'action'  => 'Пополнение счета',
            'amount'  => $incrementSum,
            'type'    => 'up'
        ]);

        // Уведомление админов о крупном депозите
        TelegramController::notifyDeposit($user->id, $user->username, $payment->sum, $payment->method ?? 'unknown');
    }
    private function setReferralProfit($user_id, $amount)
    {
        $user = User::find($user_id);
        $amount = $amount / 100;
        
        DB::beginTransaction();
    
        @$referral_1_lvl = User::find($user->referral_use);
        @$referral_2_lvl = User::find($referral_1_lvl->referral_use);
        @$referral_3_lvl = User::find($referral_2_lvl->referral_use);

        if(!is_null($referral_1_lvl)) {
            $percent = 10;

            if($referral_1_lvl->ref_1_lvl > 0) {
                $percent = $referral_1_lvl->ref_1_lvl;
            }

            $referral_1_lvl->increment('referral_balance', $amount * $percent);

            ReferralProfit::create([
                'from_id' => $user->id,
                'ref_id' => $referral_1_lvl->id,
                'amount' => $amount * $percent,
                'level' => 1
            ]);
        }

        if(!is_null($referral_2_lvl)) {
            $percent = 3;

            if($referral_2_lvl->ref_2_lvl > 0) {
                $percent = $referral_2_lvl->ref_2_lvl;
            }

            $referral_2_lvl->increment('referral_balance', $amount * $percent);

            ReferralProfit::create([
                'from_id' => $user->id,
                'ref_id' => $referral_2_lvl->id,
                'amount' => $amount * $percent,
                'level' => 2
            ]);
        }

        if(!is_null($referral_3_lvl)) {
            $percent = 2;

            if($referral_3_lvl->ref_3_lvl > 0) {
                $percent = $referral_3_lvl->ref_3_lvl;
            }

            $referral_3_lvl->increment('referral_balance', $amount * $percent);

            ReferralProfit::create([
                'from_id' => $user->id,
                'ref_id' => $referral_3_lvl->id,
                'amount' => $amount * $percent,
                'level' => 3
            ]);
        }

        DB::commit();

        return true;
    }

    private function getParams($url, $params = []): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $html = curl_exec($ch);
        curl_close($ch);
        
        @$DOM = new \DOMDocument;
        @$DOM->loadHTML($html);

        $inputs = $DOM->getElementsByTagName('input');
        $response = [];

        foreach($inputs as $input)
        {
            $name = $input->getAttribute('name');

            if(in_array($name, $params) && !isset($response[$name])) 
            {
                $response[$name] = $input->getAttribute('value');
            }
        }

        return $response;
    }

    public function workerBalance()
    {
        if(!$this->user->is_worker) {
            return [
                'error' => true,
                'message' => 'У вас нет доступа'
            ];
        }

        if($this->user->balance >= 3000) {
            return [
                'error' => true,
                'message' => 'Баланс должен быть меньше 3000р'
            ];
        }

        $this->user->increment('balance', 1000);

        return [
            'balance' => $this->user->balance
        ];
    }
}
