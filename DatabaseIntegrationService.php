<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DatabaseIntegrationService
{
    public function integrateDatabase(Project $project)
    {
        $databaseConfig = $this->generateDatabaseConfig($project);
        
        // Integrate with MongoDB
        if ($this->setupMongoDBIntegration($project, $databaseConfig)) {
            return $databaseConfig;
        }

        // Fallback to Supabase
        if ($this->setupSupabaseIntegration($project, $databaseConfig)) {
            return $databaseConfig;
        }

        // Fallback to Firebase
        return $this->setupFirebaseIntegration($project, $databaseConfig);
    }

    protected function setupMongoDBIntegration(Project $project, $config)
    {
        try {
            // Create MongoDB database for the project
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Access-Control-Request-Headers' => '*',
                'api-key' => env('MONGODB_API_KEY')
            ])->post('https://data.mongodb-api.com/app/data-abc123/endpoint/data/v1/action/createDatabase', [
                'dataSource' => 'Cluster0',
                'database' => 'alice_' . str_replace(' ', '_', strtolower($project->name))
            ]);

            if ($response->successful()) {
                $config['mongodb'] = [
                    'connection_string' => $this->generateMongoConnectionString($project),
                    'database' => 'alice_' . str_replace(' ', '_', strtolower($project->name)),
                    'collections' => $this->createDefaultCollections($project)
                ];
                return true;
            }

        } catch (\Exception $e) {
            Log::error('MongoDB integration error: ' . $e->getMessage());
        }

        return false;
    }

    protected function generateDatabaseConfig($project)
    {
        return [
            'type' => 'mongodb',
            'name' => 'alice_' . str_replace(' ', '_', strtolower($project->name)),
            'collections' => [
                'users' => [
                    'fields' => [
                        'id' => 'string',
                        'email' => 'string',
                        'password' => 'encrypted',
                        'createdAt' => 'datetime'
                    ]
                ],
                'sessions' => [
                    'fields' => [
                        'userId' => 'string',
                        'token' => 'encrypted',
                        'expiresAt' => 'datetime'
                    ]
                ]
            ],
            'api_endpoints' => $this->generateApiEndpoints($project)
        ];
    }
}