<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\User;
use App\Rank;
use App\Transaction;
use App\Withdraw;
use App\Payment;
use App\Game;
use DB;

class ProfileController extends Controller
{
    public function init()
    {
        // Расчет статистики прибыли/убытка
        $totalDeposits = Payment::where('user_id', $this->user->id)
            ->where('status', 1)
            ->sum('sum');
        
        $totalWithdraws = Withdraw::where('user_id', $this->user->id)
            ->where('status', 1)
            ->sum('sum');
        
        // Профит = выводы - депозиты (положительный = в плюсе, отрицательный = в минусе)
        $profit = $totalWithdraws - $totalDeposits;
        
        // Количество ставок из таблицы games
        $gamesPlayed = DB::table('games')->where('user_id', $this->user->id)->count();
        
        return [
            'rank' => [
                'now' => $this->rankService->get(),
                'next' => $this->rankService->next()
            ],
            'stats' => [
                'bets' => $this->user->bets,
                'deposits' => $this->user->payments()
            ],
            'financial' => [
                'totalDeposits' => $totalDeposits,
                'totalWithdraws' => $totalWithdraws,
                'profit' => $profit,
                'gamesPlayed' => $gamesPlayed,
                'wager' => $this->user->wager,
                'wagerStatus' => $this->user->wager_status
            ],
            'list' => Rank::get(),
            'cashback' => $this->user->cashback_balance
        ];
    }

    /**
     * Генерация кода для привязки Telegram
     */
    public function getTelegramLinkCode()
    {
        // Если уже привязан
        if ($this->user->tg_id && $this->user->tg_id != '0') {
            return [
                'linked' => true,
                'tg_id' => $this->user->tg_id
            ];
        }

        // Генерируем новый код если нет или истёк
        if (!$this->user->telegram_link_code) {
            $code = strtoupper(substr(md5($this->user->id . time() . rand()), 0, 8));
            $this->user->telegram_link_code = $code;
            $this->user->save();
        }

        return [
            'linked' => false,
            'code' => $this->user->telegram_link_code,
            'bot_link' => 'https://t.me/valuba_bot?start=' . $this->user->telegram_link_code
        ];
    }

    /**
     * Отвязка Telegram
     */
    public function unlinkTelegram()
    {
        $this->user->tg_id = '0';
        $this->user->telegram_link_code = null;
        $this->user->save();

        return ['success' => true];
    }

    public function cashback()
    {
        if(date('D') !== 'Mon') {
            return [
                'error' => true,
                'message' => 'Кешбек можно забрать в понедельник'
            ];
        }

        $user = User::where('id', $this->user->id)
            ->lockForUpdate()
            ->first();

        if($user->cashback_balance < 10) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма для получения кешбека - 10р'
            ];
        }

        Transaction::create([
            'user_id' => $this->user->id,
            'action'  => 'Получение кешбека',
            'amount'  => $user->cashback_balance,
            'type'    => 'up'
        ]);

        $user->balance += $user->cashback_balance;
        $user->cashback_balance = 0;
        $user->save();

        return ['balance' => $user->balance];
    }
}