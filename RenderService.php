<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RenderService
{
    protected $client;
    protected $baseUrl = 'https://api.render.com/v1';

    public function __construct()
    {
        $this->client = new Client();
    }

    public function deployToRender(Project $project, $githubRepo, $environment = 'production')
    {
        try {
            // Create Render service
            $serviceData = [
                'type' => 'web',
                'name' => $this->generateServiceName($project->name, $environment),
                'repo' => $githubRepo['clone_url'],
                'branch' => 'main',
                'buildCommand' => $this->getBuildCommand($project->type),
                'startCommand' => $this->getStartCommand($project->type),
                'plan' => $this->getPlanForEnvironment($environment),
                'autoDeploy' => true,
                'envVars' => $this->getEnvironmentVariables($project, $environment)
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('RENDER_API_KEY'),
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/services", $serviceData);

            if ($response->failed()) {
                throw new \Exception('Render deployment failed: ' . $response->body());
            }

            $service = $response->json();
            
            // Wait for deployment to complete
            $deployment = $this->waitForDeployment($service['id']);

            return [
                'id' => $service['id'],
                'url' => $service['service']['url'],
                'deployment_id' => $deployment['id'],
                'status' => $deployment['status']
            ];

        } catch (\Exception $e) {
            Log::error('Render deployment error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function waitForDeployment($serviceId, $timeout = 300)
    {
        $startTime = time();
        
        while (time() - $startTime < $timeout) {
            $deployments = $this->getServiceDeployments($serviceId);
            
            if (!empty($deployments)) {
                $latestDeployment = $deployments[0];
                
                if ($latestDeployment['status'] === 'live') {
                    return $latestDeployment;
                }
                
                if (in_array($latestDeployment['status'], ['failed', 'canceled'])) {
                    throw new \Exception('Deployment failed with status: ' . $latestDeployment['status']);
                }
            }
            
            sleep(5); // Wait 5 seconds before checking again
        }
        
        throw new \Exception('Deployment timeout exceeded');
    }

    public function getServiceDeployments($serviceId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('RENDER_API_KEY'),
            'Accept' => 'application/json'
        ])->get("{$this->baseUrl}/services/{$serviceId}/deployments");

        return $response->json();
    }

    public function createDatabase(Project $project, $databaseType = 'postgres')
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('RENDER_API_KEY'),
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/databases", [
                'name' => $this->generateDbName($project->name),
                'databaseType' => $databaseType,
                'plan' => 'starter',
                'region' => 'oregon' // Default region
            ]);

            if ($response->failed()) {
                throw new \Exception('Database creation failed: ' . $response->body());
            }

            $database = $response->json();
            
            // Wait for database to be ready
            $this->waitForDatabaseReady($database['id']);

            return $database;

        } catch (\Exception $e) {
            Log::error('Database creation error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function getBuildCommand($projectType)
    {
        $commands = [
            'web_app' => 'npm install && npm run build',
            'mobile_app' => 'npm install && npx react-native bundle --platform android --dev false',
            'website' => 'npm install',
            'game' => 'npm install',
            'ebook' => 'npm install'
        ];
        
        return $commands[$projectType] ?? 'npm install';
    }

    protected function getPlanForEnvironment($environment)
    {
        $plans = [
            'development' => 'starter',
            'staging' => 'standard',
            'production' => 'professional'
        ];
        
        return $plans[$environment] ?? 'starter';
    }
}