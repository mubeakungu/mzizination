<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    private $secretKey;
    private $publicKey;
    private $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = env('PAYSTACK_SECRET_KEY');
        $this->publicKey = env('PAYSTACK_PUBLIC_KEY');
    }

    /**
     * Initialize a Paystack transaction
     */
    public function initializeTransaction($email, $amount, $reference, $metadata = [])
    {
        $url = $this->baseUrl . '/transaction/initialize';

        $payload = [
            'email' => $email,
            'amount' => (int)($amount * 100), // Convert to kobo
            'reference' => 'MZI-' . $reference . '-' . now()->timestamp,
            'metadata' => $metadata
        ];

        try {
            $response = Http::withToken($this->secretKey)
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'reference' => $data['data']['reference'],
                    'authorization_url' => $data['data']['authorization_url'],
                    'access_code' => $data['data']['access_code']
                ];
            }

            Log::error('Paystack Initialize Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to initialize payment'
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Initialize Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify Paystack transaction
     */
    public function verifyTransaction($reference)
    {
        $url = $this->baseUrl . '/transaction/verify/' . $reference;

        try {
            $response = Http::withToken($this->secretKey)
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data['data']
                ];
            }

            Log::error('Paystack Verify Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'Verification failed'
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Verify Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get customer details
     */
    public function getCustomer($customerId)
    {
        $url = $this->baseUrl . '/customer/' . $customerId;

        try {
            $response = Http::withToken($this->secretKey)
                ->get($url);

            return $response->successful() ? [
                'success' => true,
                'data' => $response->json()['data']
            ] : [
                'success' => false,
                'error' => 'Failed to get customer'
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Get Customer Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create transfer recipient (for withdrawals)
     */
    public function createTransferRecipient($type, $accountNumber, $bankCode, $name)
    {
        $url = $this->baseUrl . '/transferrecipient';

        $payload = [
            'type' => $type, // 'nuban' for bank transfer
            'name' => $name,
            'account_number' => $accountNumber,
            'bank_code' => $bankCode,
            'currency' => 'KES'
        ];

        try {
            $response = Http::withToken($this->secretKey)
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'recipient_code' => $data['data']['recipient_code']
                ];
            }

            Log::error('Paystack Create Recipient Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to create recipient'
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Create Recipient Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Initiate transfer (withdrawal)
     */
    public function initiateTransfer($recipientCode, $amount, $reference, $reason = '')
    {
        $url = $this->baseUrl . '/transfer';

        $payload = [
            'source' => 'balance',
            'recipient' => $recipientCode,
            'amount' => (int)($amount * 100), // Convert to kobo
            'reference' => 'MZI-WITHDRAW-' . $reference,
            'reason' => $reason ?: 'Mzizination Withdrawal'
        ];

        try {
            $response = Http::withToken($this->secretKey)
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'transfer_code' => $data['data']['transfer_code'],
                    'reference' => $data['data']['reference'],
                    'status' => $data['data']['status']
                ];
            }

            Log::error('Paystack Initiate Transfer Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to initiate transfer'
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Initiate Transfer Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify transfer
     */
    public function verifyTransfer($reference)
    {
        $url = $this->baseUrl . '/transfer/verify/' . $reference;

        try {
            $response = Http::withToken($this->secretKey)
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data['data']
                ];
            }

            return [
                'success' => false,
                'error' => 'Verification failed'
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Verify Transfer Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * List banks available for transfer
     */
    public function getBanks()
    {
        $url = $this->baseUrl . '/bank?country=KE';

        try {
            $response = Http::withToken($this->secretKey)
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'banks' => $data['data']
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to fetch banks'
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Get Banks Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Resolve bank account
     */
    public function resolveAccount($accountNumber, $bankCode)
    {
        $url = $this->baseUrl . '/bank/resolve';

        try {
            $response = Http::withToken($this->secretKey)
                ->get($url, [
                    'account_number' => $accountNumber,
                    'bank_code' => $bankCode
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'account_name' => $data['data']['account_name'],
                    'account_number' => $data['data']['account_number']
                ];
            }

            Log::error('Paystack Resolve Account Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'Account resolution failed'
            ];
        } catch (\Exception $e) {
            Log::error('Paystack Resolve Account Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
