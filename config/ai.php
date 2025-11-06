<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used.
    | Supported: "openrouter", "gemini", "pollinations"
    |
    */
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'openrouter'),

    /*
    |--------------------------------------------------------------------------
    | AI Provider API Keys
    |--------------------------------------------------------------------------
    |
    | Configure your API keys for different AI providers here.
    |
    */
    'providers' => [
        'openrouter' => [
            'api_key' => env('OPENROUTER_API_KEY'),
            'default_model' => env('OPENROUTER_DEFAULT_MODEL', 'openai/gpt-3.5-turbo'),
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'default_model' => env('GEMINI_DEFAULT_MODEL', 'gemini-pro'),
        ],
        'pollinations' => [
            // Pollinations is free and doesn't require an API key
            'default_model' => 'pollinations-text',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for AI conversations
    |
    */
    'defaults' => [
        'temperature' => 0.7,
        'max_tokens' => 2000,
        'system_prompt' => 'You are a helpful AI assistant.',
    ],
];
