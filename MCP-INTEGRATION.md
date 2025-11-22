# MCP (Model Control Protocol) Integration

This document provides an overview of the MCP integration in the ALICE Pro platform.

## Table of Contents
- [Overview](#overview)
- [Available MCP Services](#available-mcp-services)
- [Usage](#usage)
  - [Using the MCP Facade](#using-the-mcp-facade)
  - [Using Dependency Injection](#using-dependency-injection)
  - [CLI Commands](#cli-commands)
- [Configuration](#configuration)
- [Adding New MCP Services](#adding-new-mcp-services)
- [Security Considerations](#security-considerations)

## Overview

The MCP (Model Control Protocol) integration allows ALICE Pro to interact with various AI models and services through a unified interface. The system includes:

- **MCP Registry**: Manages available MCP services and their configurations
- **MCP Client**: Handles communication with MCP services
- **CLI Tools**: For managing and testing MCP services
- **Facade**: For easy access to MCP functionality

## Available MCP Services

The following MCP services are pre-configured:

### Core Development
- `filesystem-mcp`: File system operations
- `git-mcp`: Git repository management
- `terminal-mcp`: Command execution in sandboxed environment
- `docker-mcp`: Container management
- `repl-mcp`: Code execution in multiple languages
- `vscode-ext-mcp`: VS Code integration
- `code-runner-mcp`: Test execution
- `linter-mcp`: Code linting and analysis

### CI/CD
- `ci-mcp`: CI/CD pipeline integration
- `registry-mcp`: Package registry access
- `deploy-mcp`: Deployment services
- `dockerhub-mcp`: Docker image management
- `terraform-mcp`: Infrastructure as code

### Database & Storage
- `postgres-mcp`: PostgreSQL database
- `supabase-mcp`: Supabase services
- `redis-mcp`: Redis key-value store
- `s3-mcp`: S3-compatible storage
- `secrets-mgr-mcp`: Secrets management

### Testing & Debugging
- `browser-console-mcp`: Headless browser automation
- `playwright-mcp`: Browser testing

## Usage

### Using the MCP Facade

```php
use App\Facades\MCP;

// List all services
$services = MCP::getAvailableServers();

// Get a client for a specific service
$client = MCP::client('openai');

// Use the client
$response = $client->complete('gpt-4', [
    ['role' => 'user', 'content' => 'Hello, world!']
]);

// Get services by category
$databaseServices = MCP::getServicesByCategory('database');

// Get services by capability
$aiServices = MCP::getServicesByCapability('ai_completion');
```

### Using Dependency Injection

```php
use App\Services\MCP\MCPRegistry;

class MyService
{
    public function __construct(
        private MCPRegistry $mcpRegistry
    ) {}

    public function doSomething()
    {
        $service = $this->mcpRegistry->getService('openai');
        // ...
    }
}
```

### CLI Commands

List all MCP services:
```bash
php artisan mcp list
```

Show details for a specific service:
```bash
php artisan mcp show openai
```

Test a service connection:
```bash
php artisan mcp test openai
```

List services by category:
```bash
php artisan mcp list --category=database
```

List services by capability:
```bash
php artisan mcp list --capability=ai_completion
```

## Configuration

MCP services are configured in `config/mcp.php` and `config/mcp-registry.json`.

### Environment Variables

```env
# Default MCP server
MCP_DEFAULT_SERVER=openai

# Server configurations
MCP_OPENAI_API_KEY=your_openai_key
MCP_ANTHROPIC_API_KEY=your_anthropic_key
MCP_HUGGINGFACE_API_KEY=your_hf_key
```

### Service Configuration

Each service in `mcp-registry.json` can have the following properties:

- `id`: Unique identifier
- `name`: Human-readable name
- `description`: Service description
- `category`: Service category
- `endpoint`: Service endpoint URL
- `capabilities`: Array of supported capabilities
- `auth`: Authentication configuration
- `protocol`: Communication protocol (http, https, ws, wss)
- `default_params`: Default parameters for requests

## Adding New MCP Services

1. Add the service to `config/mcp-registry.json`
2. Configure any required environment variables
3. Test the service using the CLI

Example service configuration:
```json
{
  "id": "my-new-service",
  "name": "My New Service",
  "description": "Description of the service",
  "category": "ai",
  "endpoint": "https://api.example.com/v1",
  "capabilities": ["ai_completion", "embeddings"],
  "auth": {
    "type": "api_key",
    "env": "MCP_MY_NEW_SERVICE_API_KEY"
  }
}
```

## Security Considerations

- **Authentication**: Always use environment variables for API keys
- **Permissions**: Restrict access to sensitive operations
- **Validation**: Validate all inputs and outputs
- **Rate Limiting**: Implement rate limiting for MCP service calls
- **Logging**: Log all MCP service interactions for auditing
