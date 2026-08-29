<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TON Payment Configuration
    |--------------------------------------------------------------------------
    */

    // API ключ от toncenter.com (бесплатный)
    'api_key' => env('TON_API_KEY', ''),

    // Кошелек для приема платежей
    'deposit_wallet' => env('TON_DEPOSIT_WALLET', ''),

    // Основной кошелек (куда переводить после подтверждения)
    'main_wallet' => env('TON_MAIN_WALLET', ''),

    // Seed фраза deposit кошелька (для автоматических переводов)
    'wallet_seed' => env('TON_WALLET_SEED', ''),

    // Минимальная сумма пополнения в рублях
    'min_amount_rub' => env('TON_MIN_AMOUNT_RUB', 12),

    // Время жизни платежа в минутах
    'payment_lifetime' => env('TON_PAYMENT_LIFETIME', 30),

    // Количество подтверждений
    'confirmations' => env('TON_CONFIRMATIONS', 1),
];
