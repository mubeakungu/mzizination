<?php

namespace App\Console\Commands;

use App\CrashGame;
use App\Services\CrashService;
use Illuminate\Console\Command;

/** Settles rounds abandoned mid-flight (e.g. auto cash-out wins after the player closed the page). */
class SettleCrashGames extends Command
{
    protected $signature = 'crash:settle';
    protected $description = 'Settle finished crash rounds';

    public function handle()
    {
        $n = 0;
        CrashGame::where('status', 'active')->chunkById(200, function ($games) use (&$n) {
            foreach ($games as $g) {
                if (CrashService::settleIfFinished($g)) $n++;
            }
        });
        $this->info("Settled $n round(s).");
        return 0;
    }
}
