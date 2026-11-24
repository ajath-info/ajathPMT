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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID', env('GOOGLE_ADS_CLIENT_ID')),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', env('GOOGLE_ADS_CLIENT_SECRET')),
        'redirect' => env('GOOGLE_REDIRECT_URI', 'https://workspace.ajath.com/auth/google/callback'),
    ],

    // Firebase web app (workspace-bb7f2). The web API key is public by design; it is used
    // server-side only to verify Google sign-in ID tokens with Google's Identity Toolkit.
    'firebase' => [
        'api_key' => env('FIREBASE_API_KEY', 'AIzaSyBJ5JcyXqo5w_BFC2piheiDHAleBtCUkik'),
        'project_id' => env('FIREBASE_PROJECT_ID', 'workspace-bb7f2'),
    ],

];
