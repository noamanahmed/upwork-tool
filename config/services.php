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
        'available_providers' => ['openai', 'anthropic', 'gemini', 'azure', 'bedrock', 'groq', 'xai', 'deepseek', 'mistral', 'ollama', 'openrouter'],
        'enabled_providers' => array_values(array_filter(
            ['openai', 'anthropic', 'gemini', 'azure', 'bedrock', 'groq', 'xai', 'deepseek', 'mistral', 'ollama', 'openrouter'],
            fn($p) => (bool) env('AI_' . strtoupper($p) . '_ENABLED', false)
        )),
        'rate_limit' => env('AI_RATE_LIMIT', 5),
        'rate_limit_interval' => env('AI_RATE_LIMIT_INTERVAL', 60),
        'max_parallel_requests' => env('AI_MAX_PARALLEL_REQUESTS', null),
        'parallel_request_timeout' => env('AI_PARALLEL_REQUEST_TIMEOUT', 60),
        'openai' => [
            'key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'conversation_id' => env('OPENAI_CONVERSATION_ID', 'openai'),
        ],
        'anthropic' => [
            'key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-opus-20240229'),
            'conversation_id' => env('ANTHROPIC_CONVERSATION_ID', 'anthropic'),
        ],
        'gemini' => [
            'key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-1.5-pro'),
            'conversation_id' => env('GEMINI_CONVERSATION_ID', 'gemini'),
        ],
        'azure' => [
            'key' => env('AZURE_API_KEY'),
            'model' => env('AZURE_MODEL', 'gpt-4'),
            'conversation_id' => env('AZURE_CONVERSATION_ID', 'azure'),
        ],
        'bedrock' => [
            'key' => env('BEDROCK_API_KEY'),
            'model' => env('BEDROCK_MODEL', 'anthropic.claude-3-sonnet-20240229-v1:0'),
            'conversation_id' => env('BEDROCK_CONVERSATION_ID', 'bedrock'),
        ],
        'groq' => [
            'key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'llama3-70b-8192'),
            'conversation_id' => env('GROQ_CONVERSATION_ID', 'groq'),
        ],
        'xai' => [
            'key' => env('XAI_API_KEY'),
            'model' => env('XAI_MODEL', 'grok-1.5'),
            'conversation_id' => env('XAI_CONVERSATION_ID', 'xai'),
        ],
        'deepseek' => [
            'key' => env('DEEPSEEK_API_KEY'),
            'model' => env('DEEPSEEK_MODEL', 'deepseek-coder'),
            'conversation_id' => env('DEEPSEEK_CONVERSATION_ID', 'deepseek'),
        ],
        'mistral' => [
            'key' => env('MISTRAL_API_KEY'),
            'model' => env('MISTRAL_MODEL', 'mistral-large-latest'),
            'conversation_id' => env('MISTRAL_CONVERSATION_ID', 'mistral'),
        ],
        'ollama' => [
            'key' => env('OLLAMA_API_KEY', ''),
            'model' => env('OLLAMA_MODEL', 'llama3'),
            'conversation_id' => env('OLLAMA_CONVERSATION_ID', 'ollama'),
        ],
        'openrouter' => [
            'key' => env('OPENROUTER_API_KEY'),
            'model' => env('OPENROUTER_MODEL', 'meta-llama/llama-3-70b-instruct'),
            'conversation_id' => env('OPENROUTER_CONVERSATION_ID', 'openrouter'),
        ],
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
