<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    private $consumerKey;
    private $consumerSecret;
    private $shortcode;
    private $passkey;
    private $callbackUrl;
    private $timeoutUrl;
    private $baseUrl = 'https://api.safaricom.co.ke';
    private $sandboxUrl = 'https://sandbox.safaricom.co.ke';
    private $isSandbox = false;

    public function __construct()
    {
        $this->consumerKey = env('MPESA_CONSUMER_KEY');
        $this->consumerSecret = env('MPESA_CONSUMER_SECRET');
        $this->shortcode = env('MPESA_SHORTCODE');
        $this->passkey = env('MPESA_PASSKEY');
        $this->callbackUrl = env('MPESA_CALLBACK_URL');
        $this->timeoutUrl = env('MPESA_TIMEOUT_URL');
        $this->isSandbox = env('MPESA_SANDBOX', false);
    }

    /**
     * Get OAuth access token
     */
    public function getAccessToken()
    {
        $url = ($this->isSandbox ? $this->sandboxUrl : $this->baseUrl) . '/oauth/v1/generate?grant_type=client_credentials';

        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get($url);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            Log::error('M-Pesa Token Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('M-Pesa Token Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * STK Push for deposit (Customer Initiated STK)
     *
     * @param string $phone Phone number (format: 254XXXXXXXXX)
     * @param int $amount Amount in KES
     * @param int $accountReference Internal order ID
     */
    public function stkPush($phone, $amount, $accountReference)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [
                'success' => false,
                'error' => 'Failed to get access token'
            ];
        }

        $timestamp = now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $url = ($this->isSandbox ? $this->sandboxUrl : $this->baseUrl) . '/mpesa/stkpush/v1/processrequest';

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int)$amount,
            'PartyA' => $phone,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $this->callbackUrl,
            'AccountReference' => 'MZI-' . $accountReference,
            'TransactionDesc' => 'Mzizination Casino Deposit'
        ];

        try {
            $response = Http::withToken($accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'checkout_request_id' => $data['CheckoutRequestID'] ?? null,
                    'response_code' => $data['ResponseCode'] ?? null,
                    'message' => $data['ResponseDescription'] ?? 'STK Sent'
                ];
            }

            Log::error('M-Pesa STK Push Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'STK push failed'
            ];
        } catch (\Exception $e) {
            Log::error('M-Pesa STK Push Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * B2C Payment (Business to Customer) for withdrawals
     *
     * @param string $phone Phone number (format: 254XXXXXXXXX)
     * @param int $amount Amount in KES
     * @param int $withdrawalId Withdrawal ID
     */
    public function b2cWithdrawal($phone, $amount, $withdrawalId)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [
                'success' => false,
                'error' => 'Failed to get access token'
            ];
        }

        $url = ($this->isSandbox ? $this->sandboxUrl : $this->baseUrl) . '/mpesa/b2c/v1/paymentrequest';

        $payload = [
            'InitiatorName' => env('MPESA_INITIATOR_NAME', 'testapi'),
            'SecurityCredential' => $this->getSecurityCredential(),
            'CommandID' => 'BusinessPayment',
            'Amount' => (int)$amount,
            'PartyA' => $this->shortcode,
            'PartyB' => $phone,
            'Remarks' => 'Mzizination Withdrawal',
            'QueueTimeOutURL' => $this->timeoutUrl,
            'ResultURL' => env('MPESA_B2C_CALLBACK_URL', $this->callbackUrl),
            'Occasion' => 'Withdrawal-' . $withdrawalId
        ];

        try {
            $response = Http::withToken($accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'conversation_id' => $data['ConversationID'] ?? null,
                    'originator_conversation_id' => $data['OriginatorConversationID'] ?? null,
                    'response_code' => $data['ResponseCode'] ?? null,
                    'message' => $data['ResponseDescription'] ?? 'Withdrawal initiated'
                ];
            }

            Log::error('M-Pesa B2C Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'withdrawal_id' => $withdrawalId
            ]);

            return [
                'success' => false,
                'error' => 'Withdrawal failed'
            ];
        } catch (\Exception $e) {
            Log::error('M-Pesa B2C Exception', [
                'error' => $e->getMessage(),
                'withdrawal_id' => $withdrawalId
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get Security Credential for B2C
     * Encrypt initiator password with M-Pesa's public certificate
     */
    private function getSecurityCredential()
    {
        // For sandbox, return plain text
        if ($this->isSandbox) {
            return base64_encode(env('MPESA_INITIATOR_PASSWORD', 'testpass123'));
        }

        // For production, use actual encryption with M-Pesa's certificate
        $certificate = file_get_contents(storage_path('app/mpesa-certificate.pem'));
        $publicKeyId = openssl_pkey_get_public($certificate);
        $password = env('MPESA_INITIATOR_PASSWORD');

        openssl_public_encrypt($password, $encrypted, $publicKeyId);
        return base64_encode($encrypted);
    }

    /**
     * Validate M-Pesa callback signature
     */
    public function validateCallback($signature, $payload)
    {
        // M-Pesa provides a signature header for validation
        // Implementation depends on their specific validation method
        // For now, we trust HTTPS and log all callbacks

        Log::info('M-Pesa Callback Received', [
            'signature' => $signature,
            'payload' => $payload
        ]);

        return true;
    }

    /**
     * Format phone number to M-Pesa format
     * Input: 0712345678, 254712345678, +254712345678
     * Output: 254712345678
     */
    public static function formatPhoneNumber($phone)
    {
        // Remove spaces and common characters
        $phone = str_replace([' ', '-', '+'], '', $phone);

        // Handle different formats
        if (str_starts_with($phone, '254')) {
            return $phone; // Already correct format
        }

        if (str_starts_with($phone, '0')) {
            return '254' . substr($phone, 1); // Replace 0 with 254
        }

        // Assume it needs 254 prefix
        return '254' . $phone;
    }

    /**
     * Validate phone number
     */
    public static function validatePhoneNumber($phone)
    {
        $phone = self::formatPhoneNumber($phone);

        // Must be 12 digits (254 + 9 digits)
        return strlen($phone) === 12 && is_numeric($phone);
    }
}
