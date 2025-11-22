<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubService
{
    protected $client;
    protected $baseUrl = 'https://api.github.com';

    public function __construct()
    {
        $this->client = new Client();
    }

    public function createRepository(Project $project)
    {
        try {
            $repoName = $this->generateRepoName($project->name);
            
            $response = Http::withHeaders([
                'Authorization' => 'token ' . env('GITHUB_TOKEN'),
                'Accept' => 'application/vnd.github.v3+json'
            ])->post($this->baseUrl . '/user/repos', [
                'name' => $repoName,
                'description' => $project->description,
                'private' => !$project->is_public,
                'auto_init' => false,
                'has_issues' => true,
                'has_projects' => true,
                'has_wiki' => true
            ]);

            if ($response->failed()) {
                throw new \Exception('GitHub repo creation failed: ' . $response->body());
            }

            $repoData = $response->json();
            
            // Push code to repository
            $this->pushCodeToRepo($repoData['clone_url'], $project);

            // Setup webhook for automatic deployments
            $this->createWebhook($repoData['owner']['login'], $repoName, $project);

            return $repoData;

        } catch (\Exception $e) {
            Log::error('GitHub integration error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function pushCodeToRepo($cloneUrl, Project $project)
    {
        // Prepare GitHub push using Git commands or GitHub API
        $zipPath = storage_path("app/projects/{$project->name}.zip");
        $tempDir = storage_path("app/temp/{$project->name}");
        
        // Extract project files
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo($tempDir);
            $zip->close();
        }

        // Initialize git repository and push
        $this->executeCommands([
            "cd {$tempDir}",
            "git init",
            "git config user.name 'ALICE'",
            "git config user.email 'noreply@alicepro.com'",
            "git add .",
            "git commit -m 'Initial commit by ALICE'",
            "git branch -M main",
            "git remote add origin {$cloneUrl}",
            "git push -u origin main"
        ]);

        // Cleanup
        $this->deleteDirectory($tempDir);
    }

    protected function createWebhook($owner, $repo, Project $project)
    {
        $webhookUrl = url('/api/webhooks/github/' . $project->id);
        
        $response = Http::withHeaders([
            'Authorization' => 'token ' . env('GITHUB_TOKEN'),
            'Accept' => 'application/vnd.github.v3+json'
        ])->post("{$this->baseUrl}/repos/{$owner}/{$repo}/hooks", [
            'name' => 'web',
            'active' => true,
            'events' => ['push', 'pull_request'],
            'config' => [
                'url' => $webhookUrl,
                'content_type' => 'json',
                'secret' => env('GITHUB_WEBHOOK_SECRET')
            ]
        ]);

        if ($response->successful()) {
            Log::info("Webhook created for project: {$project->id}");
        } else {
            Log::warning("Webhook creation failed for project: {$project->id}");
        }
    }

    public function getDeploymentStatus($owner, $repo, $deploymentId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'token ' . env('GITHUB_TOKEN'),
            'Accept' => 'application/vnd.github.v3+json'
        ])->get("{$this->baseUrl}/repos/{$owner}/{$repo}/deployments/{$deploymentId}/statuses");

        return $response->json();
    }
}