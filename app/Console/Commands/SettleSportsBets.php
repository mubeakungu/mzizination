<?php

namespace App\Console\Commands;

use App\Services\OddsApiService;
use App\Services\SportsSettlement;
use App\SportBet;
use Illuminate\Console\Command;

class SettleSportsBets extends Command
{
    protected $signature = 'sports:settle';
    protected $description = 'Settle pending sportsbook bets using results from The Odds API';

    public function handle(OddsApiService $odds)
    {
        if (!$odds->enabled()) {
            $this->warn('ODDS_API_KEY is not set; skipping.');
            return 0;
        }

        // Only matches that kicked off at least 90 minutes ago can be finished.
        $sports = SportBet::where('status', 'pending')
            ->where('commence_time', '<', now()->subMinutes(90))
            ->distinct()->pluck('sport_key');

        $settled = 0;
        foreach ($sports as $sport) {
            $results = collect($odds->scores($sport))->keyBy('id');

            $bets = SportBet::where('status', 'pending')
                ->where('sport_key', $sport)
                ->where('commence_time', '<', now()->subMinutes(90))
                ->get();

            foreach ($bets as $bet) {
                $event = $results->get($bet->event_id);
                $winner = $event ? SportsSettlement::winner($event) : null;

                if ($winner !== null) {
                    $status = $winner === $bet->selection ? 'won' : 'lost';
                    SportsSettlement::settle($bet, $status, SportsSettlement::scoreLine($event));
                    $settled++;
                } elseif ($bet->commence_time->lt(now()->subDays(3))) {
                    // Past the API's 3-day results window (postponed/cancelled): refund.
                    SportsSettlement::settle($bet, 'void', 'No result available');
                    $settled++;
                }
            }
        }

        $this->info("Settled $settled bet(s).");
        return 0;
    }
}
