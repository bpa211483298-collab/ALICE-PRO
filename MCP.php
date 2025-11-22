<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Services\MCP\MCPClient client(string $name = null)
 * @method static array getAvailableServers()
 * @method static string|null getDefaultClient()
 * 
 * @see \App\Services\MCP\MCPClient
 */
class MCP extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mcp.manager';
    }
}
