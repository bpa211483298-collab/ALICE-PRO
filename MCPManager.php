<?php

namespace App\Services\MCP;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class MCPManager
{
    /**
     * Get an MCP client instance
     *
     * @param string|null $name
     * @return MCPClient
     */
    public function client(?string $name = null): MCPClient
    {
        $name = $name ?: $this->getDefaultClient();
        $config = $this->getConfig($name);
        
        return new MCPClient($config);
    }
    
    /**
     * Get the default client name
     *
     * @return string
     */
    public function getDefaultClient(): string
    {
        return Config::get('mcp.default');
    }
    
    /**
     * Get all available server names
     *
     * @return array
     */
    public function getAvailableServers(): array
    {
        return array_keys(Config::get('mcp.servers', []));
    }
    
    /**
     * Get the configuration for a server
     *
     * @param string $name
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getConfig(string $name): array
    {
        $config = Config::get("mcp.servers.{$name}");
        
        if (is_null($config)) {
            throw new InvalidArgumentException("MCP server [{$name}] not configured.");
        }
        
        return $config;
    }
}
