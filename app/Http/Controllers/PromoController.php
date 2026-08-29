<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Promocode;
use App\PromocodeActivation;
use App\User;
use App\Payment;
use App\Transaction;

use DB;

class PromoController extends Controller
{
    public function activate(Request $request)
    {
        try {
            DB::beginTransaction();

            $code = $request->code;
            $promo = Promocode::where('name', $code)->first();
        
            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }
            
            if(!$this->isGroupMember()) {
                return [
                    'error' => true,
                    'message' => 'Подпишитесь на группу VK'
                ];
            }

            if($promo->type != 'balance') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Пополнить"'
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
        
            $this->user->increment('balance', $promo->sum);
            $this->user->increment('wager', $promo->sum * $promo->wager);
        
            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);
    
            if(PromocodeActivation::where('promo_id', $promo->id)->where('user_id', $this->user->id)->count() !== 1) {
                DB::rollback();
                return [
                    'error' => true,
                    'message' => 'Вы уже использовали этот код'
                ];
            }

            Transaction::create([
                'user_id' => $this->user->id,
                'action'  => 'Активация промокода ' . $promo->name,
                'amount'  => $promo->sum,
                'type'    => 'up'
            ]);
    
            DB::commit();
        } catch(\Exception $e) {
            DB::rollback();
            return [
                'error' => true,
                'message' => 'Подождите'
            ];
        }

        return [
            'balance' => $this->user->balance,
            'text' => 'Промокод активирован'
        ];
    }
}