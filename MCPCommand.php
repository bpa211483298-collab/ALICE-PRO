<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MCP\MCPRegistry;
use Illuminate\Support\Facades\MCP as MCPFacade;

class MCPCommand extends Command
{
    protected $signature = 'mcp 
        {action : list|show|test}
        {service? : Service ID}
        {--c|category= : Filter by category}
        {--capability= : Filter by capability}
        {--d|details : Show detailed information}
    ';

    protected $description = 'Manage MCP (Model Control Protocol) services';

    protected $registry;

    public function __construct(MCPRegistry $registry)
    {
        parent::__construct();
        $this->registry = $registry;
    }

    public function handle()
    {
        $action = $this->argument('action');
        $serviceId = $this->argument('service');
        
        switch ($action) {
            case 'list':
                return $this->listServices();
            case 'show':
                return $this->showService($serviceId);
            case 'test':
                return $this->testService($serviceId);
            default:
                $this->error("Unknown action: {$action}");
                return 1;
        }
    }

    protected function listServices()
    {
        $category = $this->option('category');
        $capability = $this->option('capability');
        $showDetails = $this->option('details');

        if ($category) {
            $services = $this->registry->getServicesByCategory($category);
            $this->line("<fg=green>Services in category '{$category}':</>");
        } elseif ($capability) {
            $services = $this->registry->getServicesByCapability($capability);
            $this->line("<fg=green>Services with capability '{$capability}':</>");
        } else {
            $services = $this->registry->getAllServices();
            $this->line('<fg=green>All MCP Services:</>');
        }

        if (empty($services)) {
            $this->line('No services found.');
            return 0;
        }

        $headers = ['ID', 'Name', 'Category', 'Capabilities'];
        $rows = [];

        foreach ($services as $service) {
            $rows[] = [
                $service['id'],
                $service['name'],
                $service['category'],
                implode(', ', array_slice($service['capabilities'], 0, 3)) . 
                    (count($service['capabilities']) > 3 ? '...' : ''),
            ];
        }

        $this->table($headers, $rows);

        if ($showDetails) {
            $this->line("\n<fg=yellow>Available categories:</> " . 
                implode(', ', $this->registry->getCategories()));
            $this->line("<fg=yellow>Available capabilities:</> " . 
                implode(', ', $this->registry->getCapabilities()));
        }

        return 0;
    }

    protected function showService(string $serviceId)
    {
        $service = $this->registry->getService($serviceId);
        
        if (!$service) {
            $this->error("Service '{$serviceId}' not found.");
            $this->line("Available services: " . 
                implode(', ', array_keys($this->registry->getAllServices())));
            return 1;
        }

        $this->line("<fg=green>Service:</> {$service['name']} ({$service['id']})");
        $this->line("<fg=yellow>Description:</> {$service['description']}");
        $this->line("<fg=yellow>Endpoint:</> {$service['endpoint']}");
        
        if (isset($service['protocol'])) {
            $this->line("<fg=yellow>Protocol:</> {$service['protocol']}");
        }
        
        $this->line("<fg=yellow>Capabilities:</> " . implode(', ', $service['capabilities']));
        
        if (isset($service['supported_languages'])) {
            $this->line("<fg=yellow>Supported Languages:</> " . 
                implode(', ', $service['supported_languages']));
        }

        if (isset($service['auth'])) {
            $this->line("<fg=yellow>Authentication:</> " . 
                $service['auth']['type'] . 
                (isset($service['auth']['env']) ? " (env: {$service['auth']['env']})" : ''));
        }

        return 0;
    }

    protected function testService(string $serviceId)
    {
        $service = $this->registry->getService($serviceId);
        
        if (!$service) {
            $this->error("Service '{$serviceId}' not found.");
            return 1;
        }

        $this->line("Testing service: <fg=yellow>{$service['name']}</>");
        
        try {
            $client = MCPFacade::client($serviceId);
            $response = $client->healthCheck();
            
            if ($response['success'] ?? false) {
                $this->line("<fg=green>✓</> Connection successful");
                $this->line("Status: " . ($response['data']['status'] ?? 'unknown'));
                return 0;
            } else {
                $this->line("<fg=red>✗</> Connection failed");
                $this->line("Error: " . ($response['error'] ?? 'Unknown error'));
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Error testing service: " . $e->getMessage());
            return 1;
        }
    }
}
