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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OAuth Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OAuth social login providers (Google, Apple, Facebook)
    | used for passwordless authentication via Laravel Socialite.
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/oauth/google/callback',
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/oauth/apple/callback',
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/oauth/facebook/callback',
    ],

    /*
    |--------------------------------------------------------------------------
    | FusionPay Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for FusionPay payment gateway used for coin deposits.
    | API URL and API key are required for payment processing.
    |
    */

    'fusionpay' => [
        'api_url' => env('FUSIONPAY_API_URL', 'https://api.fusionpay.com'),
        'api_key' => env('FUSIONPAY_API_KEY'),
    ],

];
