<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TonService
{
    protected $client;
    protected $apiKey;
    protected $depositWallet;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 30]);
        $this->apiKey = config('ton.api_key');
        $this->depositWallet = config('ton.deposit_wallet');
    }

    /**
     * Получить курс TON к RUB (с кешем)
     */
    public function getRate()
    {
        return Cache::remember('ton_rate', 180, function () {
            return $this->fetchRate();
        });
    }

    /**
     * Получить свежий курс TON (принудительное обновление кеша)
     */
    public function getFreshRate()
    {
        $rate = $this->fetchRate();
        Cache::put('ton_rate', $rate, 180);
        return $rate;
    }

    /**
     * Запросить курс с API
     */
    private function fetchRate()
    {
        try {
            $response = $this->client->get('https://api.coingecko.com/api/v3/simple/price?ids=the-open-network&vs_currencies=rub');
            $data = json_decode($response->getBody(), true);
            return $data['the-open-network']['rub'] ?? 120;
        } catch (\Exception $e) {
            Log::error('TON rate error: ' . $e->getMessage());
            return Cache::get('ton_rate', 120);
        }
    }

    /**
     * Конвертировать RUB в TON
     */
    public function rubToTon($rub)
    {
        $rate = $this->getRate();
        return $rub / $rate;
    }

    /**
     * Конвертировать TON в RUB
     */
    public function tonToRub($ton)
    {
        $rate = $this->getRate();
        return $ton * $rate;
    }

    /**
     * Получить адрес кошелька для депозитов
     */
    public function getDepositWallet()
    {
        return $this->depositWallet;
    }

    /**
     * Получить транзакции кошелька
     */
    public function getTransactions($limit = 20)
    {
        try {
            $url = "https://toncenter.com/api/v2/getTransactions?address={$this->depositWallet}&limit={$limit}&api_key={$this->apiKey}";
            $response = $this->client->get($url);
            $data = json_decode($response->getBody(), true);

            if (!isset($data['result'])) {
                return [];
            }

            $transactions = [];
            foreach ($data['result'] as $tx) {
                if (!isset($tx['in_msg']['value']) || $tx['in_msg']['value'] == 0) {
                    continue;
                }

                $transactions[] = [
                    'hash' => $tx['transaction_id']['hash'] ?? '',
                    'amount' => $tx['in_msg']['value'] / 1e9,
                    'memo' => $tx['in_msg']['message'] ?? '',
                    'timestamp' => $tx['utime'] ?? 0,
                    'from' => $tx['in_msg']['source'] ?? ''
                ];
            }

            return $transactions;
        } catch (\Exception $e) {
            Log::error('TON transactions error: ' . $e->getMessage());
            return [];
        }
    }
}
