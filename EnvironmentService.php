<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class EnvironmentService
{
    public function manageEnvironments(Project $project)
    {
        $environments = ['development', 'staging', 'production'];
        $results = [];

        foreach ($environments as $environment) {
            $results[$environment] = [
                'variables' => $this->setupEnvironmentVariables($project, $environment),
                'database' => $this->setupEnvironmentDatabase($project, $environment),
                'secrets' => $this->manageSecrets($project, $environment),
                'url' => $this->getEnvironmentUrl($project, $environment)
            ];
        }

        return $results;
    }

    protected function setupEnvironmentVariables(Project $project, $environment)
    {
        $baseVariables = [
            'NODE_ENV' => $environment,
            'APP_ENV' => $environment,
            'APP_DEBUG' => $environment === 'production' ? 'false' : 'true',
            'APP_URL' => $this->getEnvironmentUrl($project, $environment)
        ];

        $projectVariables = $project->environment_variables[$environment] ?? [];
        $databaseVariables = $this->getDatabaseVariables($project, $environment);
        
        $allVariables = array_merge($baseVariables, $projectVariables, $databaseVariables);
        
        // Encrypt sensitive variables
        foreach ($allVariables as $key => $value) {
            if ($this->isSensitive($key)) {
                $allVariables[$key] = Crypt::encryptString($value);
            }
        }

        return $allVariables;
    }

    protected function manageSecrets(Project $project, $environment)
    {
        $secrets = [
            'API_KEYS' => $this->generateApiKeys(),
            'DATABASE_CREDENTIALS' => $this->getDatabaseCredentials($project, $environment),
            'THIRD_PARTY_TOKENS' => $this->manageThirdPartyIntegrations($project)
        ];

        // Store encrypted secrets
        foreach ($secrets as $key => $value) {
            $project->secrets()->updateOrCreate([
                'environment' => $environment,
                'key' => $key
            ], [
                'value' => Crypt::encryptString(json_encode($value)),
                'type' => 'secret'
            ]);
        }

        return array_keys($secrets);
    }

    protected function setupEnvironmentDatabase(Project $project, $environment)
    {
        try {
            if ($environment === 'development') {
                // Use local or shared development database
                return $this->setupDevelopmentDatabase($project);
            } else {
                // Create dedicated database for staging/production
                return $this->createDedicatedDatabase($project, $environment);
            }
        } catch (\Exception $e) {
            Log::error("Database setup failed for {$environment}: " . $e->getMessage());
            return $this->setupFallbackDatabase($project, $environment);
        }
    }

    public function runMigrations(Project $project, $environment)
    {
        $databaseConfig = $project->database_config[$environment] ?? null;
        
        if (!$databaseConfig) {
            throw new \Exception("No database configuration for {$environment}");
        }

        // Run migrations based on project type
        if ($project->type === 'web_app') {
            return $this->runNodeMigrations($project, $databaseConfig);
        } else {
            return $this->runGenericMigrations($project, $databaseConfig);
        }
    }

    protected function runNodeMigrations(Project $project, $databaseConfig)
    {
        // Execute migration commands for Node.js projects
        $commands = [
            'npm install',
            'npx sequelize-cli db:migrate',
            'npx sequelize-cli db:seed:all'
        ];

        return $this->executeRemoteCommands($project, $commands, $databaseConfig);
    }

    public function provisionTestingEnvironment(Project $project, $pullRequestId = null)
    {
        // Create isolated testing environment for PRs or testing
        $environmentName = $pullRequestId ? "pr-{$pullRequestId}" : 'testing-' . time();
        
        try {
            $deployment = $this->deployService->deployProject($project, $environmentName);
            
            // Setup test database
            $database = $this->setupTestDatabase($project, $environmentName);
            
            // Run tests
            $testResults = $this->runTests($project, $environmentName);
            
            return [
                'environment' => $environmentName,
                'url' => $deployment['url'],
                'database' => $database,
                'test_results' => $testResults,
                'expires_at' => now()->addDays(7) // Testing environments expire after 7 days
            ];

        } catch (\Exception $e) {
            Log::error('Testing environment provision failed: ' . $e->getMessage());
            throw $e;
        }
    }
}