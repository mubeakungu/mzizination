<?php

namespace App\Http\Middleware;

use App\Services\ResponsibleGambling;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Usage: ->middleware('rg:bet'), 'rg:deposit', 'rg:play' or 'rg:withdraw'.
 * Blocks self-excluded / unverified players and enforces daily limits.
 * For 'bet', the stake is counted toward the daily total when the
 * game responds without an error.
 */
class ResponsibleGamblingGate
{
    public function handle($request, Closure $next, $action = 'play')
    {
        $user = Auth::user();
        if (!$user) return $next($request);

        // Withdrawals stay open to self-excluded players, but need a verified identity.
        if ($action === 'withdraw') {
            if ($user->kyc_status !== 'verified') {
                return response()->json(['error' => true, 'rg' => true,
                    'message' => 'Withdrawals need a verified identity. Submit your ID in Responsible Gaming; we will review it shortly.']);
            }
            return $next($request);
        }

        $amount = $request->input('amount', $request->input('bet', $request->input('sum', 0)));
        if (!is_numeric($amount)) $amount = 0;

        if ($error = ResponsibleGambling::check($user, $action, $amount)) {
            return response()->json(['error' => true, 'message' => $error, 'rg' => true]);
        }

        $response = $next($request);

        if ($action === 'bet' && $amount > 0) {
            $data = json_decode($response->getContent(), true);
            if (is_array($data) && empty($data['error'])) {
                ResponsibleGambling::recordStake($user, $amount);
            }
        }

        return $response;
    }
}
