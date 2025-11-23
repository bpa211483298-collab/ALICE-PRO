<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Config;
use App\Services\MCP\MCPRegistry;
use App\Services\MCP\MCPClient;

class MCPControllerTest extends TestCase
{
    use WithFaker, WithoutMiddleware;
    
    protected $mockRegistry;
    protected $mockClient;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test config
        Config::set('mcp', require config_path('testing/mcp.php'));
        
        // Create mock MCP client and manager
        $this->mockClient = $this->createMock(MCPClient::class);
        $this->mockManager = $this->createMock(\App\Services\MCP\MCPManager::class);
        
        // Bind mock manager to the container for the facade
        $this->app->instance('mcp.manager', $this->mockManager);
        
        // Set up default mock expectations for the manager
        $this->mockManager->method('client')
            ->willReturn($this->mockClient);
            
        $this->mockManager->method('getAvailableServers')
            ->willReturn(['local']);
            
        $this->mockManager->method('getDefaultClient')
            ->willReturn('local');
            
        // Set up default mock expectations for the client
        $this->mockClient->method('healthCheck')
            ->willReturn([
                'success' => true,
                'status' => 'ok',
                'data' => ['status' => 'ok']
            ]);
            
        $this->mockClient->method('listModels')
            ->willReturn([
                'success' => true,
                'data' => [
                    'model-1' => [
                        'id' => 'model-1',
                        'name' => 'Test Model',
                        'description' => 'A test model',
                        'capabilities' => ['completion']
                    ]
                ]
            ]);
            
        // Mock the MCP facade
        $this->app->instance('mcp.manager', $this->mockManager);
    }
    
    /** @test */
    public function it_lists_available_servers()
    {
        $response = $this->getJson('/api/mcp/servers');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'servers' => [
                '*' => ['id', 'name', 'description', 'is_default']
            ],
            'default_server'
        ]);
        
        $response->assertJsonCount(1, 'servers'); // Only the local test server is mocked
        $response->assertJsonPath('default_server', 'local');
    }
    
    /** @test */
    public function it_shows_server_details()
    {
        $response = $this->getJson('/api/mcp/servers/local');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id', 'name', 'description', 'is_default'
        ]);
        $response->assertJsonPath('id', 'local');
        $response->assertJsonPath('name', 'Local Test Server');
        
        // Should not expose sensitive information
        $response->assertJsonMissing(['api_key']);
    }
    
    /** @test */
    public function it_tests_server_connection()
    {
        $response = $this->getJson('/api/mcp/servers/local/test');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'ok',
            'data' => ['status' => 'ok']
        ]);
    }
    
    /** @test */
    public function it_lists_models()
    {
        $response = $this->getJson('/api/mcp/servers/local/models');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'models',
            'server'
        ]);
        
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('server', 'local');
    }
    
    /** @test */
    public function it_handles_missing_server()
    {
        $response = $this->getJson('/api/mcp/servers/nonexistent');
        
        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'Server not found'
        ]);
    }
}
