<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\User;

class MCPTest extends TestCase
{
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user without persisting to database
        $this->user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Test MCP services page loads with services list
     */
    public function test_mcp_servers_list()
    {
        $response = $this->actingAs($this->user)
                        ->getJson('/api/mcp/servers');
                        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'endpoint',
                'status'
            ]
        ]);
    }

    /**
     * Test MCP service logs
     */
    public function test_mcp_server_details()
    {
        // First, get a server ID from the list
        $listResponse = $this->actingAs($this->user)
                           ->getJson('/api/mcp/servers');
                           
        if (count($listResponse->json()) > 0) {
            $serverId = $listResponse->json()[0]['id'];
            
            $response = $this->actingAs($this->user)
                           ->getJson("/api/mcp/servers/{$serverId}");
                           
            $response->assertStatus(200);
            $response->assertJsonStructure([
                'id',
                'name',
                'endpoint',
                'status',
                'models',
                'capabilities'
            ]);
        } else {
            $this->markTestSkipped('No MCP servers available for testing');
        }
    }

    /**
     * Test MCP service status check
     */
    public function test_mcp_server_connection_test()
    {
        // First, get a server ID from the list
        $listResponse = $this->actingAs($this->user)
                           ->getJson('/api/mcp/servers');
                           
        if (count($listResponse->json()) > 0) {
            $serverId = $listResponse->json()[0]['id'];
            
            $response = $this->actingAs($this->user)
                           ->getJson("/api/mcp/servers/{$serverId}/test");
                           
            $response->assertStatus(200);
            $response->assertJson([
                'success' => true,
                'message' => 'Connection successful'
            ]);
        } else {
            $this->markTestSkipped('No MCP servers available for testing');
        }
    }

    /**
     * Test MCP service control (start/stop/restart)
     */
    public function test_mcp_list_models()
    {
        // First, get a server ID from the list
        $listResponse = $this->actingAs($this->user)
                           ->getJson('/api/mcp/servers');
                           
        if (count($listResponse->json()) > 0) {
            $serverId = $listResponse->json()[0]['id'];
            
            $response = $this->actingAs($this->user)
                           ->getJson("/api/mcp/servers/{$serverId}/models");
                           
            $response->assertStatus(200);
            $response->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'description',
                    'capabilities'
                ]
            ]);
        } else {
            $this->markTestSkipped('No MCP servers available for testing');
        }
    }

    /**
     * Test MCP service configuration
     */
    public function test_mcp_model_completion()
    {
        // First, get a server ID and model ID
        $serversResponse = $this->actingAs($this->user)
                              ->getJson('/api/mcp/servers');
                              
        if (count($serversResponse->json()) > 0) {
            $serverId = $serversResponse->json()[0]['id'];
            
            $modelsResponse = $this->actingAs($this->user)
                                 ->getJson("/api/mcp/servers/{$serverId}/models");
                                 
            if (count($modelsResponse->json()) > 0) {
                $modelId = $modelsResponse->json()[0]['id'];
                
                $response = $this->actingAs($this->user)
                               ->postJson("/api/mcp/servers/{$serverId}/models/{$modelId}/complete", [
                                   'prompt' => 'Hello, world!',
                                   'max_tokens' => 50
                               ]);
                               
                $response->assertStatus(200);
                $response->assertJsonStructure([
                    'id',
                    'model',
                    'choices' => [
                        '*' => [
                            'text',
                            'index',
                            'logprobs',
                            'finish_reason'
                        ]
                    ]
                ]);
            } else {
                $this->markTestSkipped('No models available for testing');
            }
        } else {
            $this->markTestSkipped('No MCP servers available for testing');
        }
    }
}
