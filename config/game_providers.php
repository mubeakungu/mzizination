<?php

/*
 * Licensed third-party games (seamless wallet).
 *
 * A provider is only shown to players once its secret AND launch URL are set,
 * i.e. after you have signed a contract and received credentials.
 *
 * launch_url is a template; {token}, {game}, {operator}, {currency}, {lang},
 * {lobby} are replaced. Use the exact format from the provider's docs.
 */
return [
    'currency' => env('PROVIDER_CURRENCY', 'KES'),
    'session_minutes' => env('PROVIDER_SESSION_MINUTES', 240),

    'providers' => [
        'spribe' => [
            'name' => 'Spribe',
            'secret' => env('SPRIBE_SECRET'),
            'operator' => env('SPRIBE_OPERATOR'),
            'launch_url' => env('SPRIBE_LAUNCH_URL'),
            'games' => ['aviator' => 'Aviator'],
        ],
        'smartsoft' => [
            'name' => 'SmartSoft Gaming',
            'secret' => env('SMARTSOFT_SECRET'),
            'operator' => env('SMARTSOFT_OPERATOR'),
            'launch_url' => env('SMARTSOFT_LAUNCH_URL'),
            'games' => ['jetx' => 'JetX'],
        ],
        'aviatrix' => [
            'name' => 'Aviatrix',
            'secret' => env('AVIATRIX_SECRET'),
            'operator' => env('AVIATRIX_OPERATOR'),
            'launch_url' => env('AVIATRIX_LAUNCH_URL'),
            'games' => ['aviatrix' => 'Aviatrix'],
        ],
    ],
];
