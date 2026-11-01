<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin client for Safaricom's Daraja API: STK push (deposits), STK query
 * (to confirm a deposit really went through) and B2C (withdrawals).
 */
class MpesaService
{
    public function baseUrl(): string
    {
        return config('mpesa.sandbox') ? 'https://sandbox.safaricom.co.ke' : 'https://api.safaricom.co.ke';
    }

    public function isConfigured(): bool
    {
        return config('mpesa.consumer_key') && config('mpesa.consumer_secret')
            && config('mpesa.shortcode') && config('mpesa.passkey') && config('mpesa.callback_secret');
    }

    public function b2cConfigured(): bool
    {
        return $this->isConfigured() && config('mpesa.initiator_name') && config('mpesa.initiator_password')
            && (config('mpesa.certificate') || is_file((string) config('mpesa.certificate_path')));
    }

    public function callbackUrl(string $path): string
    {
        return rtrim((string) config('mpesa.callback_base'), '/') . '/mpesa/callback/' . config('mpesa.callback_secret') . '/' . $path;
    }

    public function getAccessToken(): ?string
    {
        // Tokens last an hour; a null result isn't kept, so a failure is retried next time.
        return Cache::remember('mpesa_token_' . md5((string) config('mpesa.consumer_key')), 3000, function () {
            $response = Http::timeout(15)
                ->withBasicAuth((string) config('mpesa.consumer_key'), (string) config('mpesa.consumer_secret'))
                ->get($this->baseUrl() . '/oauth/v1/generate', ['grant_type' => 'client_credentials']);

            if (!$response->successful() || !$response->json('access_token')) {
                Log::error('M-Pesa token error', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            return $response->json('access_token');
        });
    }

    private function stkPassword(string $timestamp): string
    {
        return base64_encode(config('mpesa.shortcode') . config('mpesa.passkey') . $timestamp);
    }

    private function post(string $path, array $payload): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['ok' => false, 'status' => 0, 'body' => ['errorMessage' => 'Could not authenticate with M-Pesa']];
        }

        try {
            $response = Http::timeout(30)->withToken($token)->post($this->baseUrl() . $path, $payload);
        } catch (\Throwable $e) {
            Log::error('M-Pesa request failed', ['path' => $path, 'error' => $e->getMessage()]);
            return ['ok' => false, 'status' => 0, 'body' => ['errorMessage' => 'M-Pesa is not reachable']];
        }

        return ['ok' => $response->successful(), 'status' => $response->status(), 'body' => $response->json() ?? []];
    }

    /**
     * Ask the customer's phone for their M-Pesa PIN.
     * Returns ['success' => bool, 'checkout_request_id' => ?, 'message' => string].
     */
    public function stkPush(string $phone, int $amount, string $reference): array
    {
        $timestamp = now('Africa/Nairobi')->format('YmdHis');

        $r = $this->post('/mpesa/stkpush/v1/processrequest', [
            'BusinessShortCode' => config('mpesa.shortcode'),
            'Password' => $this->stkPassword($timestamp),
            'Timestamp' => $timestamp,
            'TransactionType' => config('mpesa.transaction_type'),
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => config('mpesa.party_b') ?: config('mpesa.shortcode'),
            'PhoneNumber' => $phone,
            'CallBackURL' => $this->callbackUrl('stk'),
            'AccountReference' => substr((string) config('mpesa.account_reference'), 0, 12),
            'TransactionDesc' => substr('Deposit ' . $reference, 0, 13),
        ]);

        $b = $r['body'];
        if ($r['ok'] && ($b['ResponseCode'] ?? null) === '0' && !empty($b['CheckoutRequestID'])) {
            return ['success' => true, 'checkout_request_id' => $b['CheckoutRequestID'], 'message' => $b['CustomerMessage'] ?? 'Check your phone'];
        }

        Log::warning('M-Pesa STK push rejected', ['response' => $b, 'status' => $r['status']]);
        return ['success' => false, 'checkout_request_id' => null, 'message' => $b['errorMessage'] ?? $b['ResponseDescription'] ?? 'M-Pesa request failed'];
    }

    /**
     * Ask Safaricom for the outcome of an STK push.
     * Returns ['state' => 'paid'|'failed'|'pending', 'code' => ?, 'desc' => string].
     */
    public function stkQuery(string $checkoutRequestId): array
    {
        $timestamp = now('Africa/Nairobi')->format('YmdHis');

        $r = $this->post('/mpesa/stkpushquery/v1/query', [
            'BusinessShortCode' => config('mpesa.shortcode'),
            'Password' => $this->stkPassword($timestamp),
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ]);

        $b = $r['body'];
        if (isset($b['ResultCode'])) {
            $code = (string) $b['ResultCode'];
            return ['state' => $code === '0' ? 'paid' : 'failed', 'code' => $code, 'desc' => $b['ResultDesc'] ?? ''];
        }

        // "The transaction is being processed" (500.001.1001), rate limits and outages
        return ['state' => 'pending', 'code' => null, 'desc' => $b['errorMessage'] ?? 'pending'];
    }

    /**
     * Send money to a customer. Returns ['success' => bool, 'conversation_id', 'originator_conversation_id', 'message'].
     */
    public function b2c(string $phone, int $amount, string $occasion): array
    {
        $credential = $this->securityCredential();
        if (!$credential) {
            return ['success' => false, 'message' => 'M-Pesa B2C certificate or initiator password is missing'];
        }

        $r = $this->post('/mpesa/b2c/v1/paymentrequest', [
            'InitiatorName' => config('mpesa.initiator_name'),
            'SecurityCredential' => $credential,
            'CommandID' => 'BusinessPayment',
            'Amount' => $amount,
            'PartyA' => config('mpesa.b2c_shortcode'),
            'PartyB' => $phone,
            'Remarks' => 'Mzizination withdrawal',
            'QueueTimeOutURL' => $this->callbackUrl('b2c/timeout'),
            'ResultURL' => $this->callbackUrl('b2c/result'),
            'Occasion' => substr($occasion, 0, 100),
        ]);

        $b = $r['body'];
        if ($r['ok'] && ($b['ResponseCode'] ?? null) === '0') {
            return [
                'success' => true,
                'conversation_id' => $b['ConversationID'] ?? null,
                'originator_conversation_id' => $b['OriginatorConversationID'] ?? null,
                'message' => $b['ResponseDescription'] ?? 'Accepted',
            ];
        }

        Log::warning('M-Pesa B2C rejected', ['response' => $b, 'status' => $r['status']]);
        return ['success' => false, 'message' => $b['errorMessage'] ?? $b['ResponseDescription'] ?? 'M-Pesa B2C request failed'];
    }

    /** Initiator password encrypted with Safaricom's public certificate. */
    private function securityCredential(): ?string
    {
        $pem = config('mpesa.certificate') ?: (is_file((string) config('mpesa.certificate_path')) ? file_get_contents(config('mpesa.certificate_path')) : null);
        $password = config('mpesa.initiator_password');
        if (!$pem || !$password) return null;

        $key = openssl_pkey_get_public(str_replace('\n', "\n", $pem));
        if (!$key || !openssl_public_encrypt($password, $encrypted, $key, OPENSSL_PKCS1_PADDING)) {
            Log::error('M-Pesa certificate could not be used to encrypt the initiator password');
            return null;
        }

        return base64_encode($encrypted);
    }

    /** 0712345678, 712345678, +254712345678, 254112345678 -> 2547XXXXXXXX / 2541XXXXXXXX */
    public static function formatPhoneNumber($phone): string
    {
        $phone = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($phone, '254')) return $phone;
        if (str_starts_with($phone, '0')) return '254' . substr($phone, 1);
        return '254' . $phone;
    }

    /** Safaricom numbers are 2547XXXXXXXX or 2541XXXXXXXX. */
    public static function validatePhoneNumber($phone): bool
    {
        return (bool) preg_match('/^254(7|1)\d{8}$/', self::formatPhoneNumber($phone));
    }

    /** Pull "Name"/"Value" or "Key"/"Value" lists out of a callback into an array. */
    public static function items(?array $list, string $keyField = 'Name'): array
    {
        $out = [];
        foreach ($list ?? [] as $item) {
            if (isset($item[$keyField])) $out[$item[$keyField]] = $item['Value'] ?? null;
        }
        return $out;
    }
}
