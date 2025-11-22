<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default MCP Server
    |--------------------------------------------------------------------------
    |
    | This option controls the default MCP server that will be used when
    | interacting with MCP services. You can override this on a per-request
    | basis if needed.
    |
    */

    'default' => env('MCP_DEFAULT_SERVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | MCP Servers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection details for each MCP server that
    | is used by your application. You can add as many servers as needed.
    |
    */

    'servers' => [
        'local' => [
            'name' => 'Local MCP Server',
            'url' => env('MCP_LOCAL_URL', 'http://localhost:8080'),
            'api_key' => env('MCP_LOCAL_API_KEY', ''),
            'description' => 'Local development MCP server',
        ],
        'openai' => [
            'name' => 'OpenAI Compatible',
            'url' => env('MCP_OPENAI_URL', 'https://api.openai.com/v1'),
            'api_key' => env('MCP_OPENAI_API_KEY', ''),
            'description' => 'OpenAI API compatible endpoint',
        ],
        'anthropic' => [
            'name' => 'Anthropic',
            'url' => env('MCP_ANTHROPIC_URL', 'https://api.anthropic.com/v1'),
            'api_key' => env('MCP_ANTHROPIC_API_KEY', ''),
            'description' => 'Anthropic Claude API',
        ],
        'huggingface' => [
            'name' => 'Hugging Face',
            'url' => env('MCP_HUGGINGFACE_URL', 'https://api-inference.huggingface.co/models'),
            'api_key' => env('MCP_HUGGINGFACE_API_KEY', ''),
            'description' => 'Hugging Face Inference API',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | This option controls the maximum number of seconds to wait for a response
    | from the MCP server before timing out.
    |
    */

    'timeout' => env('MCP_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the retry behavior for failed MCP requests.
    |
    */

    'retry' => [
        'max_attempts' => env('MCP_RETRY_ATTEMPTS', 3),
        'delay' => env('MCP_RETRY_DELAY', 100), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the caching behavior for MCP responses.
    |
    */

    'cache' => [
        'enabled' => env('MCP_CACHE_ENABLED', true),
        'ttl' => env('MCP_CACHE_TTL', 3600), // seconds
        'store' => env('MCP_CACHE_STORE', 'file'),
    ],
];
