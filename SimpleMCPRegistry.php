<?php

namespace App\Services\MCP;

class SimpleMCPRegistry
{
    protected array $registry = [];
    protected string $registryPath;
    protected string $schemaPath;

    public function __construct(string $registryPath, ?string $schemaPath = null)
    {
        $this->registryPath = $registryPath;
        $this->schemaPath = $schemaPath ?: __DIR__ . '/../../../config/schemas/mcp-service.schema.json';
        $this->loadRegistry();
    }

    protected function loadRegistry(): void
    {
        if (!file_exists($this->registryPath)) {
            throw new \RuntimeException("MCP registry not found at: {$this->registryPath}");
        }

        $registryData = file_get_contents($this->registryPath);
        if ($registryData === false) {
            throw new \RuntimeException("Failed to read registry file: {$this->registryPath}");
        }

        $registry = json_decode($registryData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON in MCP registry: " . json_last_error_msg());
        }

        $this->validateRegistry($registry);
        $this->registry = $this->indexRegistry($registry);
    }

    protected function validateRegistry(array $registry): void
    {
        if (!isset($registry['version']) || !is_string($registry['version'])) {
            throw new \RuntimeException("Invalid registry: missing or invalid 'version' field");
        }

        if (!isset($registry['services']) || !is_array($registry['services'])) {
            throw new \RuntimeException("Invalid registry: missing or invalid 'services' field");
        }

        foreach ($registry['services'] as $service) {
            if (!isset($service['id'], $service['name'], $service['endpoint'], $service['capabilities'])) {
                throw new \RuntimeException("Invalid service entry: missing required fields");
            }
        }
    }

    protected function indexRegistry(array $registry): array
    {
        $indexed = [];
        foreach ($registry['services'] as $service) {
            $indexed[$service['id']] = $service;
        }
        return $indexed;
    }

    public function getService(string $id): ?array
    {
        return $this->registry[$id] ?? null;
    }

    public function getAllServices(): array
    {
        return $this->registry;
    }

    public function getServicesByCategory(string $category): array
    {
        return array_filter($this->registry, function($service) use ($category) {
            return ($service['category'] ?? null) === $category;
        });
    }

    public function getCategories(): array
    {
        $categories = [];
        foreach ($this->registry as $service) {
            if (isset($service['category']) && !in_array($service['category'], $categories, true)) {
                $categories[] = $service['category'];
            }
        }
        return $categories;
    }
}
