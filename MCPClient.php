<?php

namespace App\Services\MCP;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class MCPClient
{
    protected Client $client;
    protected string $baseUrl;
    protected string $apiKey;
    protected array $defaultHeaders = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ];

    public function __construct(string $baseUrl, string $apiKey = '')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl . '/',
            'timeout' => 60,
            'verify' => false, // For development only, use proper SSL in production
        ]);

        if (!empty($this->apiKey)) {
            $this->defaultHeaders['Authorization'] = 'Bearer ' . $this->apiKey;
        }
    }

    /**
     * Send a request to the MCP server
     */
    public function request(string $method, string $endpoint, array $data = []): array
    {
        try {
            $options = [
                'headers' => $this->defaultHeaders,
                'json' => $data,
            ];

            $response = $this->client->request($method, $endpoint, $options);
            
            return [
                'success' => true,
                'status' => $response->getStatusCode(),
                'data' => json_decode($response->getBody()->getContents(), true),
            ];
        } catch (GuzzleException $e) {
            Log::error('MCP Request Failed: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => $e->getCode(),
            ];
        }
    }

    /**
     * List available models on the MCP server
     */
    public function listModels(): array
    {
        return $this->request('GET', '/v1/models');
    }

    /**
     * Generate completions using a specific model
     */
    public function complete(string $model, array $messages, array $params = []): array
    {
        return $this->request('POST', '/v1/chat/completions', array_merge([
            'model' => $model,
            'messages' => $messages,
        ], $params));
    }

    /**
     * Get model information
     */
    public function getModel(string $modelId): array
    {
        return $this->request('GET', "/v1/models/{$modelId}");
    }

    /**
     * Check server health
     */
    public function healthCheck(): array
    {
        return $this->request('GET', '/health');
    }
}
