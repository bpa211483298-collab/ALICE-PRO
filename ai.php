<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used by the
    | framework when an AI service is needed. You may set this to
    | any of the providers defined in the "providers" array below.
    |
    */

    'default' => env('AI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the AI providers for your application as
    | well as their configurations. You can add additional providers
    | as needed for your application.
    |
    */

    'providers' => [
        'openai' => [
            'driver' => 'openai',
            'api_key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
        ],
        'anthropic' => [
            'driver' => 'anthropic',
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-opus'),
        ],
        'google' => [
            'driver' => 'google',
            'api_key' => env('GOOGLE_AI_API_KEY'),
            'model' => env('GOOGLE_AI_MODEL', 'gemini-pro'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Optimization Settings
    |--------------------------------------------------------------------------
    |
    | These settings control how the AI optimization service behaves.
    |
    */

    'optimization' => [
        'enabled' => env('AI_OPTIMIZATION_ENABLED', true),
        'cache_ttl' => env('AI_OPTIMIZATION_CACHE_TTL', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Selection Strategy
    |--------------------------------------------------------------------------
    |
    | This option determines how the AI service selects the most appropriate
    | model for a given task. Options: 'auto', 'fixed', 'cost_optimized'
    |
    */

    'model_selection' => env('AI_MODEL_SELECTION', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Default Model Capabilities
    |--------------------------------------------------------------------------
    |
    | Define the default capabilities for different types of AI models.
    | These will be used when model-specific capabilities are not defined.
    |
    */

    'default_capabilities' => [
        'text' => [
            'max_tokens' => 4000,
            'temperature' => 0.7,
        ],
        'code' => [
            'max_tokens' => 4000,
            'temperature' => 0.2,
        ],
        'image' => [
            'size' => '1024x1024',
            'quality' => 'standard',
        ],
    ],
];
