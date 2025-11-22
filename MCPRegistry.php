<?php

namespace App\Services\MCP;

use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use Illuminate\Validation\ValidationException;

// Simple cache implementation for when Laravel's cache is not available
class SimpleCache
{
    private static $store = [];
    
    public static function rememberForever($key, $callback)
    {
        if (!isset(self::$store[$key])) {
            self::$store[$key] = $callback();
        }
        return self::$store[$key];
    }
    
    public static function forget($key)
    {
        unset(self::$store[$key]);
    }
}

class MCPRegistry
{
    protected array $registry = [];
    protected string $registryPath;
    protected string $schemaPath;

    public function __construct(?string $registryPath = null, ?string $schemaPath = null)
    {
        try {
            if ($registryPath === null) {
                // Try to use Laravel's config if available
                if (function_exists('config_path') && function_exists('config')) {
                    $this->registryPath = config('mcp.registry_path', config_path('mcp-registry.json'));
                    $this->schemaPath = config('mcp.schema_path', config_path('schemas/mcp-service.schema.json'));
                } else {
                    // Fallback to default paths
                    $this->registryPath = realpath(__DIR__ . '/../../../config/mcp-registry.json');
                    $this->schemaPath = realpath(__DIR__ . '/../../../config/schemas/mcp-service.schema.json');
                    
                    if ($this->registryPath === false || $this->schemaPath === false) {
                        throw new \RuntimeException("Failed to resolve default paths for registry or schema files");
                    }
                }
            } else {
                $this->registryPath = realpath($registryPath);
                if ($this->registryPath === false) {
                    throw new \RuntimeException("Registry file not found: $registryPath");
                }
                
                $this->schemaPath = $schemaPath ? realpath($schemaPath) : realpath(__DIR__ . '/../../../config/schemas/mcp-service.schema.json');
                if ($this->schemaPath === false) {
                    throw new \RuntimeException("Schema file not found: " . ($schemaPath ?? 'default schema path'));
                }
            }
            
            $this->loadRegistry();
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to initialize MCPRegistry: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Load and validate the MCP registry
     */
    protected function loadRegistry(): void
    {
        $cacheKey = 'mcp.registry.v' . md5_file($this->registryPath);
        
        $cache = class_exists('Illuminate\\Support\\Facades\\Cache') 
            ? 'Illuminate\\Support\\Facades\\Cache' 
            : SimpleCache::class;
            
        $this->registry = $cache::rememberForever($cacheKey, function () {
            if (!file_exists($this->registryPath)) {
                throw new \RuntimeException("MCP registry not found at: {$this->registryPath}");
            }

            $registry = json_decode(file_get_contents($this->registryPath), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException("Invalid JSON in MCP registry: " . json_last_error_msg());
            }

            $this->validateRegistry($registry);
            return $this->indexRegistry($registry);
        });
    }

    /**
     * Validate the registry against the schema
     */
    protected function validateRegistry(array $registry): void
    {
        if (!file_exists($this->schemaPath)) {
            throw new \RuntimeException("MCP registry schema not found at: {$this->schemaPath}");
        }

        // Skip validation if we don't have access to the validator
        if (!class_exists('Illuminate\\Validation\\Factory')) {
            return;
        }

        $validator = new Validator(
            app('translator'),
            $registry,
            [
                'version' => 'required|string',
                'services' => 'required|array',
                'services.*.id' => 'required|string|distinct',
                'services.*.name' => 'required|string',
                'services.*.endpoint' => 'required|url',
                'services.*.capabilities' => 'required|array',
                'services.*.capabilities.*' => 'string',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Index the registry by ID for faster lookups
     */
    protected function indexRegistry(array $registry): array
    {
        $indexed = [
            'version' => $registry['version'] ?? '1.0.0',
            'by_id' => [],
            'by_category' => [],
            'by_capability' => [],
        ];

        foreach ($registry['services'] as $service) {
            $serviceId = $service['id'];
            $category = $service['category'] ?? 'uncategorized';
            
            // Index by ID
            $indexed['by_id'][$serviceId] = $service;
            
            // Index by category
            if (!isset($indexed['by_category'][$category])) {
                $indexed['by_category'][$category] = [];
            }
            $indexed['by_category'][$category][] = $serviceId;
            
            // Index by capabilities
            foreach ($service['capabilities'] ?? [] as $capability) {
                if (!isset($indexed['by_capability'][$capability])) {
                    $indexed['by_capability'][$capability] = [];
                }
                $indexed['by_capability'][$capability][] = $serviceId;
            }
        }

        return $indexed;
    }

    /**
     * Get all services
     */
    public function getAllServices(): array
    {
        return $this->registry['by_id'] ?? [];
    }

    /**
     * Get a service by ID
     */
    public function getService(string $serviceId): ?array
    {
        return $this->registry['by_id'][$serviceId] ?? null;
    }

    /**
     * Get services by category
     */
    public function getServicesByCategory(string $category): array
    {
        $serviceIds = $this->registry['by_category'][$category] ?? [];
        return array_intersect_key($this->registry['by_id'], array_flip($serviceIds));
    }

    /**
     * Get services by capability
     */
    public function getServicesByCapability(string $capability): array
    {
        $serviceIds = $this->registry['by_capability'][$capability] ?? [];
        return array_intersect_key($this->registry['by_id'], array_flip($serviceIds));
    }

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        return array_keys($this->registry['by_category'] ?? []);
    }

    /**
     * Get all capabilities
     */
    public function getCapabilities(): array
    {
        return array_keys($this->registry['by_capability'] ?? []);
    }

    /**
     * Check if a service exists
     */
    public function hasService(string $serviceId): bool
    {
        return isset($this->registry['by_id'][$serviceId]);
    }

    /**
     * Check if a service has a specific capability
     */
    public function hasCapability(string $serviceId, string $capability): bool
    {
        $service = $this->getService($serviceId);
        return $service && in_array($capability, $service['capabilities'] ?? []);
    }

    /**
     * Get the registry version
     */
    public function getVersion(): string
    {
        return $this->registry['version'] ?? '1.0.0';
    }

    /**
     * Reload the registry
     */
    public function reload(): void
    {
        Cache::forget('mcp.registry');
        $this->loadRegistry();
    }
}
