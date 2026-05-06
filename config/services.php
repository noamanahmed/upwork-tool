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
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'slack' => [
        'webhook_url' => env('SLACK_WEBHOOK_URL'),
    ],
    'ai' => [
        'provider' => env('AI_PROVIDER', 'openai'),
        'rate_limit' => env('AI_RATE_LIMIT', 5),
        'rate_limit_interval' => env('AI_RATE_LIMIT_INTERVAL', 60),
        'max_parallel_requests' => env('AI_MAX_PARALLEL_REQUESTS', null),
        'parallel_request_timeout' => env('AI_PARALLEL_REQUEST_TIMEOUT', 60),
        'openai' => [
            'key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'conversation_id' => env('OPENAI_CONVERSATION_ID', 'openai'),
        ],
        'gemini' => [
            'key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-1.5-pro'),
            'conversation_id' => env('GEMINI_CONVERSATION_ID', 'gemini')
        ]
    ],

    'auth0' => [
        'domain' => env('AUTH0_DOMAIN'),
        'client_id' => env('AUTH0_CLIENT_ID'),
        'client_secret' => env('AUTH0_CLIENT_SECRET'),
        'base_url' => env('AUTH0_BASE_URL'),
        'redirect' => env('AUTH0_REDIRECT_URI', 'http://localhost:10999/login/auth0/callback'),
        'audience' => env('AUTH0_AUDIENCE'),
    ],
];
