<?php

namespace App\Http\Controllers;

use App\Facades\MCP;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MCPController extends Controller
{
    /**
     * List all available MCP servers
     */
    public function index(): JsonResponse
    {
        $servers = [];
        $config = config('mcp.servers', []);
        
        foreach (MCP::getAvailableServers() as $serverId) {
            $servers[$serverId] = [
                'id' => $serverId,
                'name' => $config[$serverId]['name'] ?? $serverId,
                'description' => $config[$serverId]['description'] ?? '',
                'is_default' => $serverId === config('mcp.default'),
            ];
        }

        return response()->json([
            'servers' => array_values($servers),
            'default_server' => config('mcp.default'),
        ]);
    }

    /**
     * Get details of a specific MCP server
     */
    public function show(string $serverId): JsonResponse
    {
        $config = config("mcp.servers.{$serverId}");
        
        if (!$config) {
            return response()->json([
                'error' => 'Server not found',
            ], 404);
        }

        // Don't expose sensitive information
        unset($config['api_key']);
        
        $config['is_default'] = $serverId === config('mcp.default');
        $config['id'] = $serverId;

        return response()->json($config);
    }

    /**
     * Test connection to an MCP server
     */
    public function testConnection(string $serverId): JsonResponse
    {
        try {
            $client = MCP::client($serverId);
            $response = $client->healthCheck();
            
            return response()->json([
                'success' => $response['success'] ?? false,
                'status' => $response['status'] ?? 'unknown',
                'data' => $response['data'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'server' => $serverId,
            ], 500);
        }
    }

    /**
     * List models available on an MCP server
     */
    public function listModels(string $serverId): JsonResponse
    {
        try {
            $client = MCP::client($serverId);
            $response = $client->listModels();
            
            return response()->json([
                'success' => $response['success'] ?? false,
                'models' => $response['data'] ?? [],
                'server' => $serverId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'server' => $serverId,
            ], 500);
        }
    }

    /**
     * Get information about a specific model
     */
    public function getModel(string $serverId, string $modelId): JsonResponse
    {
        try {
            $client = MCP::client($serverId);
            $response = $client->getModel($modelId);
            
            return response()->json([
                'success' => $response['success'] ?? false,
                'model' => $response['data'] ?? null,
                'server' => $serverId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'server' => $serverId,
                'model' => $modelId,
            ], 500);
        }
    }

    /**
     * Generate completions using a model
     */
    public function complete(Request $request, string $serverId, string $modelId): JsonResponse
    {
        $validated = $request->validate([
            'messages' => 'required|array',
            'messages.*.role' => 'required|string|in:system,user,assistant',
            'messages.*.content' => 'required|string',
            'temperature' => 'sometimes|numeric|between:0,2',
            'max_tokens' => 'sometimes|integer|min:1',
        ]);

        try {
            $client = MCP::client($serverId);
            $response = $client->complete(
                $modelId,
                $validated['messages'],
                $request->except(['messages', 'server', 'model'])
            );
            
            return response()->json([
                'success' => $response['success'] ?? false,
                'data' => $response['data'] ?? null,
                'server' => $serverId,
                'model' => $modelId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'server' => $serverId,
                'model' => $modelId,
            ], 500);
        }
    }
}
