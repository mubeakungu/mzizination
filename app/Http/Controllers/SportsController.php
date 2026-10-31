<?php

namespace App\Http\Controllers;

use App\Services\OddsApiService;
use App\SportBet;
use App\Transaction;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SportsController extends Controller
{
    protected $odds;

    public function __construct(OddsApiService $odds)
    {
        parent::__construct();
        $this->odds = $odds;
    }

    public function sports()
    {
        if (!$this->odds->enabled()) {
            return ['error' => true, 'message' => 'Sportsbook is not configured (missing ODDS_API_KEY)'];
        }

        return ['sports' => $this->odds->sports()];
    }

    public function events(Request $request)
    {
        $sport = (string) $request->input('sport');
        if (!preg_match('/^[a-z0-9_]+$/', $sport)) {
            return ['error' => true, 'message' => 'Invalid sport'];
        }

        $events = array_values(array_filter($this->odds->events($sport), function ($e) {
            return Carbon::parse($e['commence_time'])->isFuture();
        }));

        return ['events' => $events];
    }

    public function bet(Request $request)
    {
        $min = (float) config('services.odds_api.min_bet');
        $max = (float) config('services.odds_api.max_bet');

        $validator = Validator::make($request->all(), [
            'sport' => 'required|string|regex:/^[a-z0-9_]+$/',
            'event_id' => 'required|string|max:64',
            'selection' => 'required|string|max:255',
            'amount' => "required|numeric|min:$min|max:$max",
        ]);

        if ($validator->fails()) {
            return ['error' => true, 'message' => $validator->errors()->first()];
        }

        // Price comes from the server's cached feed, never from the client.
        $event = $this->odds->findEvent($request->sport, $request->event_id);
        if (!$event) {
            return ['error' => true, 'message' => 'This match is no longer available'];
        }

        if (!Carbon::parse($event['commence_time'])->isFuture()) {
            return ['error' => true, 'message' => 'Betting is closed for this match'];
        }

        $outcome = collect($event['outcomes'])->firstWhere('name', $request->selection);
        if (!$outcome) {
            return ['error' => true, 'message' => 'Invalid selection'];
        }

        $amount = round((float) $request->amount, 2);

        DB::beginTransaction();

        $user = User::where('id', $this->user->id)->lockForUpdate()->first();

        if ($user->ban) {
            DB::rollBack();
            return ['error' => true, 'message' => 'Your account is blocked'];
        }

        if ($amount > $user->balance) {
            DB::rollBack();
            return ['error' => true, 'message' => 'Insufficient balance'];
        }

        $user->decrement('balance', $amount);

        $bet = SportBet::create([
            'user_id' => $user->id,
            'event_id' => $event['id'],
            'sport_key' => $event['sport_key'],
            'sport_title' => $event['sport_title'],
            'home_team' => $event['home_team'],
            'away_team' => $event['away_team'],
            'commence_time' => Carbon::parse($event['commence_time']),
            'selection' => $outcome['name'],
            'odds' => $outcome['price'],
            'amount' => $amount,
            'potential_win' => round($amount * $outcome['price'], 2),
            'status' => 'pending',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'action' => "Sports bet #{$bet->id}: {$event['home_team']} vs {$event['away_team']} ({$outcome['name']} @ {$outcome['price']})",
            'amount' => $amount,
            'type' => 'down',
        ]);

        DB::commit();

        return [
            'success' => true,
            'balance' => $user->fresh()->balance,
            'bet' => $bet,
        ];
    }

    public function myBets()
    {
        return [
            'bets' => SportBet::where('user_id', $this->user->id)->orderByDesc('id')->limit(50)->get(),
        ];
    }
}
