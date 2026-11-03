<?php

/*
| M-Pesa (Safaricom Daraja) settings. Get the keys from https://developer.safaricom.co.ke
| (sandbox) and, once Safaricom approves your paybill/till for "Lipa na M-Pesa Online"
| and B2C, from the production app on the same portal.
*/
return [
    'sandbox' => env('MPESA_SANDBOX', true),

    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),

    // Lipa na M-Pesa Online (STK push) for deposits
    'shortcode' => env('MPESA_SHORTCODE'),          // paybill, or the store number for a till
    'passkey' => env('MPESA_PASSKEY'),
    'transaction_type' => env('MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline'), // or CustomerBuyGoodsOnline
    'party_b' => env('MPESA_PARTY_B'),              // till number when using CustomerBuyGoodsOnline

    // B2C for withdrawals
    'b2c_shortcode' => env('MPESA_B2C_SHORTCODE', env('MPESA_SHORTCODE')),
    'initiator_name' => env('MPESA_INITIATOR_NAME'),
    'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),
    // Safaricom's public certificate (sandbox or production), PEM. Either the
    // file path or the PEM text itself.
    'certificate_path' => env('MPESA_CERT_PATH', storage_path('app/mpesa-certificate.pem')),
    'certificate' => env('MPESA_CERT'),

    // Callbacks. Daraja doesn't sign callbacks, so the URL carries a secret and
    // every successful deposit is re-checked with Safaricom before it is credited.
    'callback_secret' => env('MPESA_CALLBACK_SECRET'),
    'callback_base' => env('MPESA_CALLBACK_BASE', env('APP_URL')),

    // Amounts in KES (M-Pesa only takes whole shillings)
    'min_deposit' => (int) env('MPESA_MIN_DEPOSIT', 10),
    'max_deposit' => (int) env('MPESA_MAX_DEPOSIT', 150000),
    'min_withdraw' => (int) env('MPESA_MIN_WITHDRAW', 50),
    'max_withdraw' => (int) env('MPESA_MAX_WITHDRAW', 150000),

    'account_reference' => env('MPESA_ACCOUNT_REFERENCE', 'Mzizination'),
];
