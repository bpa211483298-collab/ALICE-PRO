<?php

namespace App\Providers;

use App\Services\MCP\MCPClient;
use App\Services\MCP\MCPRegistry;
use App\Services\AIOptimizationService;
use App\Services\AIOrchestrationService;
use Illuminate\Support\ServiceProvider;

class MCPServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/ai.php', 'ai'
        );
        
        $this->mergeConfigFrom(
            __DIR__.'/../../config/mcp.php', 'mcp'
        );

        // Register AI Optimization Service
        $this->app->singleton(AIOptimizationService::class, function ($app) {
            return new AIOptimizationService();
        });

        // Register AI Orchestration Service
        $this->app->singleton(AIOrchestrationService::class, function ($app) {
            return new AIOrchestrationService(
                $app->make(AIOptimizationService::class)
            );
        });

        // Register MCP Registry
        $this->app->singleton(MCPRegistry::class, function ($app) {
            return new MCPRegistry();
        });

        // Register MCP Manager
        $this->app->singleton('mcp.manager', function ($app) {
            return new class($app) {
                protected $clients = [];
                protected $defaultClient = null;
                protected $registry;
                protected $config;

                public function __construct($app)
                {
                    $this->config = $app['config']->get('mcp', []);
                    $this->defaultClient = $this->config['default'] ?? null;
                    $this->registry = $app->make(MCPRegistry::class);
                }

                /**
                 * Get an MCP client instance
                 */
                public function client(string $name = null): ?MCPClient
                {
                    $name = $name ?: $this->defaultClient;
                    
                    if (!isset($this->clients[$name])) {
                        if (!isset($this->config['servers'][$name])) {
                            throw new \RuntimeException("MCP server '{$name}' is not configured.");
                        }

                        $config = $this->config['servers'][$name];
                        $this->clients[$name] = new MCPClient(
                            $config['url'],
                            $config['api_key'] ?? ''
                        );
                    }

                    return $this->clients[$name];
                }

                /**
                 * Get all configured MCP servers
                 */
                public function getAvailableServers(): array
                {
                    return array_keys($this->config['servers'] ?? []);
                }

                /**
                 * Get the default client name
                 */
                public function getDefaultClient(): ?string
                {
                    return $this->defaultClient;
                }
            };
        });

        // Register facade accessor
        $this->app->bind('mcp', function ($app) {
            return $app->make('mcp.manager');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/mcp.php' => config_path('mcp.php'),
        ], 'config');
    }
}
