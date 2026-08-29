<?php

namespace App\Http\Controllers;

use App\User;
use App\Slot;
use App\SlotsData;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

class COPYController extends Controller
{
    protected $secret = 'HokZFDGDSJkw';

    public function callback($method, Request $request)
    {
        switch($method) {
            case 'trx.cancel':
                return $this->trxCancel($request);
            break;

            case 'trx.complete':
                return $this->trxComplete($request);
            break;

            case 'check.session':
                return $this->checkSession($request);
            break;

            case 'check.balance':
                return $this->checkBalance($request);
            break;

            case 'withdraw.bet':
                return $this->userBet($request);
            break;

            case 'deposit.win':
                return $this->userWin($request);
            break;

            default:
                throw new \Exception("Unknown method");
        }
    }

    private function trxCancel($data) {
        return response()->json(['status' => 200]);
    }

    private function trxComplete($data) {
        return response()->json(['status' => 200]);
    }

    private function checkSession($data) {
        if(!$data->session) return response()->json([
            'status' => 404, 
            'method' => 'check.session', 
            'message' => 'Unknown session'
        ]);

        $user = User::where('api_token', $data->session)->first();

        if(!$user) return response()->json([
            'status' => 404, 
            'method' => 
            'check.session', 
            'message' => 'Unknown user'
        ]);

        return response()->json([
            'status' => 200, 
            'method' => 'check.session', 
            'response' => [
                'id_player' => $user->id, 
                'id_group' => 'default', 
                'balance' => round($user->balance * 100)
            ]
        ]);
    }

    private function checkBalance($data) {
        if(!$data->session) return response()->json([
            'status' => 404, 
            'method' => 'check.balance', 
            'message' => 'Unknown session'
        ]);

        $user = User::where('api_token', $data->session)->first();

        if(!$user) return response()->json([
            'status' => 404, 
            'method' => 'check.balance', 
            'message' => 'Unknown user'
        ]);

        return response()->json([
            'status' => 200, 
            'method' => 'check.balance', 
            'response' => [
                'currency' => 'RUB', 
                'balance' => round($user->balance * 100)
            ]
        ]);
    }

    public function userBet($data) {
        if(!$data->session) return response()->json([
            'status' => 404, 
            'method' => 'check.balance', 
            'message' => 'Unknown session'
        ]);

        $bet = SlotsData::where('trx_id', $data->trx_id)->first();
        if($bet) return response()->json([
            'status' => 404, 
            'method' => 'check.balance', 
            'message' => 'Bet already exists'
        ]); 

        $user = User::where('api_token', $data->session)->first();
        if(!$user) return response()->json([
            'status' => 404, 
            'method' => 'check.balance', 
            'message' => 'Unknown user'
        ]);

        $user->balance -= $data->amount / 100;
        $user->bets += $data->amount / 100;
        $user->save();

        SlotsData::create([
            'user_id' => $user->id,
            'trx_id' => $data->trx_id,
            'game_id' => $user->current_id,
            'amount' => $data->amount / 100,
            'type' => 'bet',
            'balanceBefore' => $user->balance + ($data->amount / 100),
            'balanceAfter' => $user->balance
        ]);

        // обновляем ранг
        $this->rankService->update($user);

        return response()->json([
            'status' => 200, 
            'method' => 'withdraw.bet', 
            'response' => [
                'currency' => 'RUB', 
                'balance' => round($user->balance * 100)
            ]
        ]);
    }

    public function userWin($data) {
        if(!$data->session) return response()->json([
            'status' => 404, 
            'method' => 'check.session', 
            'message' => 'Unknown session'
        ]);

        $user = User::where('api_token', $data->session)->first();

        if(!$user) return response()->json([
            'status' => 404, 
            'method' => 'check.session', 
            'message' => 'Unknown user'
        ]);

        $user->balance += $data->amount / 100;
        $user->save();

        if($data->amount > 0) {
            SlotsData::create([
                'user_id' => $user->id,
                'trx_id' => $data->trx_id,
                'game_id' => $user->current_id,
                'amount' => $data->amount / 100,
                'type' => 'win',
                'balanceBefore' => $user->balance - ($data->amount / 100),
                'balanceAfter' => $user->balance
            ]);
        }

        return response()->json([
            'status' => 200, 
            'method' => 'withdraw.bet', 
            'response' => [
                'currency' => 'RUB', 
                'balance' => round($user->balance * 100)
            ]
        ]);
    }

    public function parse()
    {
        $data = json_decode(file_get_contents(public_path() . "/copy_slots.json"), true);
        $slots = $data['response'];

        Slot::where('provider_type', 'COPY')->delete();

        foreach($slots as $slot) {
            Slot::create([
                'game_id' => $slot['id'],
                'title' => $slot['title'],
                'icon' => $slot['icon'],
                'provider_type' => 'COPY',
                'provider' => $slot['provider'],
                'show' => 1,
            ]);
        }

        return 'OK';
    }
}