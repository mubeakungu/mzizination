<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use App\Payment;
use Illuminate\Http\Request;
use Str;
use Auth;
use App\Rank;
use App\Faqs;
use App\WithdrawSystems;

class UserController extends Controller
{
    public function init()
    {
        if(!$this->user) {
            return [
                'message' => 'Вы не авторизованы'
            ];
        }

        return [
            'user' => [
                'id' => $this->user->id,
                'unique_id' => $this->user->unique_id,
                'balance' => $this->user->balance,
                'avatar' => $this->user->avatar,
                'username' => $this->user->username,
                'date' => $this->user->created_at,
                'vk_id' => $this->user->vk_id,
                'tg_id' => $this->user->tg_id,
                'is_worker' => $this->user->is_worker,
                'rank' => $this->rankService->get(),
                'nextRank' => $this->rankService->next(),
                'stats' => [
                    'deposits' => $this->user->payments(),
                    'bets' => $this->user->bets
                ]
            ],
            'config' => [
                'tg_channel' => $this->config->tg_channel,
                'tg_bot' => $this->config->tg_bot,
                'vk_url' => $this->config->vk_url
            ]
        ];
    }

    public function ranks()
    {
        return Rank::orderBy('id', 'asc')->get();
    }

    public function faqs()
    {
        return Faqs::orderBy('id', 'asc')->get();
    }

    public function methods()
    {
        return WithdrawSystems::orderBy('id', 'asc')->get();
    }
    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    public function success()
    {
        return view('success');
    }

    public function play()
    {
        return view('play');
    }
}
