<?php

namespace App\Http\Controllers;

use App\Services\ProviderWallet;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    /** Games available to players (only providers with credentials). */
    public function catalog()
    {
        return ['games' => ProviderWallet::catalog()];
    }

    /** Player opens a game: returns the provider URL to load in an iframe. */
    public function launch(Request $request)
    {
        $provider = (string) $request->provider;
        $game = (string) $request->game;

        if (!ProviderWallet::enabled($provider) || !isset(ProviderWallet::provider($provider)['games'][$game])) {
            return ['error' => true, 'message' => 'This game is not available yet'];
        }

        return ['url' => ProviderWallet::launchUrl($this->user, $provider, $game, '/licensed')];
    }

    /**
     * Server-to-server wallet callback from the provider.
     * POST /provider/{provider}/wallet, JSON body, header X-Signature = HMAC-SHA256(body, secret).
     * Body: { action: balance|bet|win|rollback, token, transaction_id, round_id, game, amount, reference_transaction_id }
     */
    public function wallet(Request $request, $provider)
    {
        if (!ProviderWallet::enabled($provider)) {
            return response()->json(['status' => 'error', 'code' => 'UNKNOWN_PROVIDER'], 404);
        }

        if (!ProviderWallet::verifySignature($provider, $request->getContent(), $request->header('X-Signature'))) {
            return response()->json(['status' => 'error', 'code' => 'INVALID_SIGNATURE'], 401);
        }

        $d = $request->json()->all();
        $user = ProviderWallet::sessionUser($provider, $d['token'] ?? null);
        if (!$user) return response()->json(['status' => 'error', 'code' => 'INVALID_TOKEN']);

        $action = $d['action'] ?? '';
        if ($action !== 'balance' && empty($d['transaction_id'])) {
            return response()->json(['status' => 'error', 'code' => 'MISSING_TRANSACTION_ID']);
        }

        switch ($action) {
            case 'balance':  return ProviderWallet::balance($user);
            case 'bet':      return ProviderWallet::bet($provider, $user, $d);
            case 'win':      return ProviderWallet::win($provider, $user, $d);
            case 'rollback': return ProviderWallet::rollback($provider, $user, $d);
        }

        return response()->json(['status' => 'error', 'code' => 'UNKNOWN_ACTION']);
    }
}
