<?php

return [
    'default' => 'local',
    'registry_path' => storage_path('framework/testing/mcp-registry.json'),
    'schema_path' => storage_path('framework/testing/mcp-service.schema.json'),

    'servers' => [
        'local' => [
            'name' => 'Local Test Server',
            'url' => 'http://localhost:8080',
            'api_key' => 'test-api-key',
            'description' => 'Local test server',
        ],
        'test' => [
            'name' => 'Test Server',
            'url' => 'http://test-server:8080',
            'api_key' => 'test-api-key-2',
            'description' => 'Test server',
        ],
    ],
];
