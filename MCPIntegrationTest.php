<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\User;
use App\Facades\MCP;

class MCPIntegrationTest extends TestCase
{
    use WithFaker, WithoutMiddleware;

    protected $user;
    protected $testServiceConfig = [
        'test-service' => [
            'name' => 'Test Service',
            'description' => 'A test service for MCP',
            'endpoint' => 'http://localhost:8080/api',
            'timeout' => 30,
            'retry_attempts' => 3,
            'retry_delay' => 1000,
            'auth' => [
                'type' => 'bearer',
                'token' => 'test-token-12345'
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user without database
        $this->user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
        
        // Mock configuration for testing
        config(['mcp.services' => $this->testServiceConfig]);
    }

    /**
     * Test that the MCP facade can be resolved
     */
    public function test_mcp_facade_can_be_resolved()
    {
        $this->assertInstanceOf(\App\Services\MCP\MCPClient::class, MCP::client());
    }

    /**
     * Test getting available servers
     */
    public function test_get_available_servers()
    {
        $servers = MCP::getAvailableServers();
        
        $this->assertIsArray($servers);
        $this->assertArrayHasKey('test-service', $servers);
        $this->assertEquals('Test Service', $servers['test-service']['name']);
    }

    /**
     * Test getting the default client
     */
    public function test_get_default_client()
    {
        $defaultClient = MCP::getDefaultClient();
        
        $this->assertNotNull($defaultClient);
        $this->assertIsString($defaultClient);
    }

    /**
     * Test creating a client with a specific service name
     */
    public function test_create_client_with_specific_service()
    {
        $client = MCP::client('test-service');
        
        $this->assertInstanceOf(\App\Services\MCP\MCPClient::class, $client);
        $this->assertEquals('test-service', $client->getServiceName());
    }

    /**
     * Test the MCP service endpoint is accessible
     */
    public function test_mcp_service_endpoint_is_accessible()
    {
        $response = $this->actingAs($this->user)
                        ->get('/mcp/services');
                        
        $response->assertStatus(200);
        $response->assertViewIs('mcp.services');
    }

    /**
     * Test service status endpoint returns valid structure
     */
    public function test_service_status_endpoint()
    {
        $response = $this->actingAs($this->user)
                        ->get('/mcp/services/status');
                        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'services' => [
                '*' => [
                    'id',
                    'name',
                    'status',
                    'endpoint',
                    'last_checked',
                    'response_time'
                ]
            ]
        ]);
    }

    /**
     * Test service discovery functionality
     */
    public function test_service_discovery()
    {
        $response = $this->actingAs($this->user)
                        ->postJson('/mcp/discover', [
                            'endpoint' => 'http://localhost:8080',
                            'timeout' => 5
                        ]);
                        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'services',
            'message'
        ]);
    }

    /**
     * Test service registration
     */
    public function test_service_registration()
    {
        $serviceData = [
            'name' => 'New Test Service',
            'endpoint' => 'http://localhost:8081',
            'type' => 'api',
            'config' => [
                'auth' => [
                    'type' => 'none'
                ]
            ]
        ];
        
        $response = $this->actingAs($this->user)
                        ->postJson('/mcp/register', $serviceData);
                        
        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Service registered successfully'
        ]);
    }

    /**
     * Test service health check
     */
    public function test_service_health_check()
    {
        $response = $this->actingAs($this->user)
                        ->get('/mcp/health/test-service');
                        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'service',
            'timestamp',
            'metrics' => [
                'uptime',
                'memory_usage',
                'cpu_usage'
            ]
        ]);
    }
}
