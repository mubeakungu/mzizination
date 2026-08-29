<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\ReferralProfit;
use App\User;

use DB;

class ReferralController extends Controller
{
    public function init()
    {
        $referral_lvl_1_list = User::where('referral_use', $this->user->id)
            ->select('id')
            ->get();
        
        $referral_lvl_2_list = User::whereIn('referral_use', $referral_lvl_1_list)
            ->select('id')
            ->get();

        $referral_lvl_3_list = User::whereIn('referral_use', $referral_lvl_2_list)
            ->select('id')
            ->get();

        return [
            'data' => [
                'lvl_1' => [
                    'count' => count($referral_lvl_1_list),
                    'income' => ReferralProfit::whereIn('from_id', $referral_lvl_1_list)->where('level', 1)->sum('amount')
                ],
                'lvl_2' => [
                    'count' => count($referral_lvl_2_list),
                    'income' => ReferralProfit::whereIn('from_id', $referral_lvl_2_list)->where('level', 2)->sum('amount')
                ],
                'lvl_3' => [
                    'count' => count($referral_lvl_3_list),
                    'income' => ReferralProfit::whereIn('from_id', $referral_lvl_3_list)->where('level', 3)->sum('amount')
                ],
                'incomeAll' => ReferralProfit::whereIn('from_id', $referral_lvl_1_list)->where('level', 1)->sum('amount') + ReferralProfit::whereIn('from_id', $referral_lvl_2_list)->where('level', 2)->sum('amount') + ReferralProfit::whereIn('from_id', $referral_lvl_3_list)->where('level', 3)->sum('amount'),
                'refAll' => count($referral_lvl_1_list) + count($referral_lvl_2_list) + count($referral_lvl_3_list),
            ],
            'ref_income' => $this->user->referral_balance,
            'ref_reward' => $this->config->referral_reward,
            'link' => $this->config->referral_domain . '/r/' . $this->user->unique_id
        ];
    }

    public function take()
    {
        DB::beginTransaction();

        $user = User::where('id', $this->user->id)->lockForUpdate()->first();
        
        if($user->referral_balance < 20) {
            return [
                'error' => true,
                'message' => 'Минимальный вывод 20 ₽'
            ];
        }

        $user->balance += $user->referral_balance;
        $user->wager += $user->referral_balance * 3;
        $user->referral_balance = 0;
        $user->save();

        DB::commit();

        return [
            'balance' => $user->balance
        ];
    }

    public function setReferral($unique_id)
    {
        Session(['ref' => $unique_id]);
        return redirect('/');
    }
}
