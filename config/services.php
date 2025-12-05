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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    // Paymob payment gateway configuration
    'paymob' => [
        'api_key' => env('PAYMOB_API_KEY'),
        'integration_id' => env('PAYMOB_INTEGRATION_ID'),
        'iframe_id' => env('PAYMOB_IFRAME_ID'),
        'base_url' => env('PAYMOB_BASE_URL', 'https://accept.paymob.com'),
        'webhook_secret' => env('PAYMOB_WEBHOOK_SECRET'),
    ],

    // BOSTA shipping service configuration
    'bosta' => [
        'api_key' => env('BOSTA_API_KEY'),
        'sandbox' => env('BOSTA_SANDBOX', true),
        'webhook_secret' => env('BOSTA_WEBHOOK_SECRET'),

        // Pickup address (your warehouse/store location)
        'pickup_address' => [
            'first_line' => env('BOSTA_PICKUP_ADDRESS_LINE1', ''),
            'second_line' => env('BOSTA_PICKUP_ADDRESS_LINE2', ''),
            'city' => env('BOSTA_PICKUP_CITY', 'Cairo'),
            'zone' => env('BOSTA_PICKUP_ZONE', ''),
            'district' => env('BOSTA_PICKUP_DISTRICT', ''),
        ],

        // Business location
        'business_location_id' => env('BOSTA_BUSINESS_LOCATION_ID'),

        // Default package settings
        'default_package_type' => env('BOSTA_PACKAGE_TYPE', 'Parcel'),
        'default_package_size' => env('BOSTA_PACKAGE_SIZE', 'SMALL'), // SMALL, MEDIUM, LARGE
        'default_pickup_slot' => env('BOSTA_PICKUP_SLOT', '10:00 to 13:00'),
        'allow_open_package' => env('BOSTA_ALLOW_OPEN_PACKAGE', false),
    ],


];
