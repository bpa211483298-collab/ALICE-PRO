<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class InstantDeploymentService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function deployToRender(Project $project)
    {
        try {
            $zipPath = storage_path("app/projects/{$project->name}.zip");
            
            // Upload to GitHub first (simplified)
            $githubUrl = $this->uploadToGithub($project, $zipPath);
            
            // Deploy to Render
            $response = $this->client->post('https://api.render.com/v1/services', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('RENDER_API_KEY'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'name' => $project->name,
                    'type' => 'web',
                    'repo' => $githubUrl,
                    'branch' => 'main',
                    'buildCommand' => 'npm install && npm run build',
                    'startCommand' => 'npm start',
                    'envVars' => $this->getEnvironmentVariables($project)
                ]
            ]);

            $deploymentData = json_decode($response->getBody()->getContents(), true);
            
            return [
                'status' => 'deployed',
                'url' => $deploymentData['service']['url'],
                'deployment_id' => $deploymentData['service']['id']
            ];

        } catch (\Exception $e) {
            Log::error('Render deployment error: ' . $e->getMessage());
            return $this->deployToNetlify($project); // Fallback
        }
    }

    public function deployToNetlify(Project $project)
    {
        try {
            $zipPath = storage_path("app/projects/{$project->name}.zip");
            
            $response = $this->client->post('https://api.netlify.com/api/v1/sites', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('NETLIFY_TOKEN'),
                    'Content-Type' => 'application/zip',
                ],
                'body' => fopen($zipPath, 'r')
            ]);

            $deploymentData = json_decode($response->getBody()->getContents(), true);
            
            return [
                'status' => 'deployed',
                'url' => $deploymentData['url'],
                'deployment_id' => $deploymentData['id']
            ];

        } catch (\Exception $e) {
            Log::error('Netlify deployment error: ' . $e->getMessage());
            return $this->deployToVercel($project); // Fallback
        }
    }
}