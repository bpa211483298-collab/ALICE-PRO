<?php

namespace App\Services\MCP;

class SimpleMCPClient
{
    protected string $endpoint;
    protected ?string $apiKey;
    protected array $defaultHeaders = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ];

    public function __construct(string $endpoint, ?string $apiKey = null)
    {
        $this->endpoint = rtrim($endpoint, '/');
        $this->apiKey = $apiKey;
        
        if ($this->apiKey) {
            $this->defaultHeaders['Authorization'] = 'Bearer ' . $this->apiKey;
        }
    }

    /**
     * List all repositories
     */
    public function listRepositories(): array
    {
        return $this->request('GET', '/api/repositories');
    }

    /**
     * Get repository details
     */
    public function getRepository(string $name): array
    {
        return $this->request('GET', "/api/repositories/" . urlencode($name));
    }

    /**
     * List branches for a repository
     */
    public function listBranches(string $repository): array
    {
        return $this->request('GET', "/api/repositories/" . urlencode($repository) . "/branches");
    }

    /**
     * Create a new branch
     */
    public function createBranch(string $repository, string $branchName, string $sourceBranch = 'main'): array
    {
        return $this->request('POST', "/api/repositories/" . urlencode($repository) . "/branches", [
            'name' => $branchName,
            'source' => $sourceBranch
        ]);
    }

    /**
     * Make an HTTP request to the MCP service
     */
    protected function request(string $method, string $path, array $data = []): array
    {
        $url = $this->endpoint . $path;
        $headers = [];
        
        // Prepare headers
        foreach ($this->defaultHeaders as $key => $value) {
            $headers[] = "$key: $value";
        }
        
        // Initialize cURL
        $ch = curl_init();
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        
        // Set request method and data
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($response === false) {
            throw new \RuntimeException("cURL error: $error");
        }
        
        // Decode JSON response
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Failed to decode JSON response: " . json_last_error_msg());
        }
        
        // Check for API errors
        if ($httpCode >= 400) {
            $errorMessage = $result['message'] ?? 'Unknown error';
            throw new \RuntimeException("API error ($httpCode): $errorMessage");
        }
        
        return $result;
    }
}
