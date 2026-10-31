<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'vkontakte' => [
        'client_id' => env('VKONTAKTE_CLIENT_ID'),
        'client_secret' => env('VKONTAKTE_CLIENT_SECRET'),
        'redirect' => env('VKONTAKTE_REDIRECT_URI'),
        'lang' => 'ru'
    ],
    // The Odds API (free tier: 500 credits/month) powers the sportsbook.
    'odds_api' => [
        'key' => env('ODDS_API_KEY'),
        'base_url' => env('ODDS_API_BASE_URL', 'https://api.the-odds-api.com/v4'),
        'region' => env('ODDS_API_REGION', 'eu'),
        // Comma-separated sport keys to show; empty shows every in-season sport.
        'sports' => env('ODDS_API_SPORTS', 'soccer_epl,soccer_uefa_champs_league,soccer_spain_la_liga,basketball_nba'),
        'cache_minutes' => env('ODDS_API_CACHE_MINUTES', 15),
        'min_bet' => env('SPORTS_MIN_BET', 10),
        'max_bet' => env('SPORTS_MAX_BET', 100000),
    ],
];
