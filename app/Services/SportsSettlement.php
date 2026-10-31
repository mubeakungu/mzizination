<?php

namespace App\Services;

use App\SportBet;
use App\Transaction;
use App\User;
use DB;

class SportsSettlement
{
    /**
     * Winning outcome name for a completed event from The Odds API /scores,
     * or null if it can't be determined. A level score settles as "Draw".
     */
    public static function winner(array $event)
    {
        if (empty($event['completed']) || empty($event['scores'])) return null;

        $scores = [];
        foreach ($event['scores'] as $s) {
            if (!is_numeric($s['score'] ?? null)) return null;
            $scores[$s['name']] = (float) $s['score'];
        }

        $home = $scores[$event['home_team']] ?? null;
        $away = $scores[$event['away_team']] ?? null;
        if ($home === null || $away === null) return null;

        if ($home > $away) return $event['home_team'];
        if ($away > $home) return $event['away_team'];
        return 'Draw';
    }

    public static function scoreLine(array $event)
    {
        $by = collect($event['scores'] ?? [])->pluck('score', 'name');
        return $event['home_team'] . ' ' . $by->get($event['home_team']) . ' - ' . $by->get($event['away_team']) . ' ' . $event['away_team'];
    }

    /** Settle one pending bet as won, lost or void (refund). Returns the new status. */
    public static function settle(SportBet $bet, $status, $result = null)
    {
        return DB::transaction(function () use ($bet, $status, $result) {
            $bet = SportBet::where('id', $bet->id)->lockForUpdate()->first();
            if ($bet->status !== 'pending') return $bet->status;

            $payout = 0;
            if ($status === 'won') $payout = $bet->potential_win;
            if ($status === 'void') $payout = $bet->amount;

            if ($payout > 0) {
                $user = User::where('id', $bet->user_id)->lockForUpdate()->first();
                $user->increment('balance', $payout);

                Transaction::create([
                    'user_id' => $bet->user_id,
                    'action' => ($status === 'won' ? 'Sports bet won' : 'Sports bet refunded') . " #{$bet->id}",
                    'amount' => $payout,
                    'type' => 'up',
                ]);
            }

            $bet->update(['status' => $status, 'result' => $result, 'settled_at' => now()]);

            return $status;
        });
    }
}
