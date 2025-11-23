<?php

use App\Http\Controllers\API\AiController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\DeploymentController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // AI Endpoints
    Route::post('/ai/generate', [AiController::class, 'generateProject']);
    Route::post('/ai/voice-command', [AiController::class, 'processVoiceCommand']);

    // Project Endpoints
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    Route::get('/projects/{id}/download', [ProjectController::class, 'download']);

    // Deployment Endpoints
    Route::post('/deploy/{projectId}', [DeploymentController::class, 'deploy']);
    Route::get('/deployments', [DeploymentController::class, 'index']);
    Route::get('/deployments/{id}/status', [DeploymentController::class, 'status']);

    // User Endpoints
    Route::get('/user', [UserController::class, 'profile']);
    Route::put('/user', [UserController::class, 'update']);
});

// Public endpoints
Route::get('/public/projects', [ProjectController::class, 'publicIndex']);
Route::get('/public/projects/{id}', [ProjectController::class, 'publicShow']);


// MCP Server Management
Route::prefix('mcp')->group(function () {
    Route::get('/servers', [\App\Http\Controllers\MCPController::class, 'index']);
    Route::get('/servers/{serverId}', [\App\Http\Controllers\MCPController::class, 'show']);
    Route::get('/servers/{serverId}/test', [\App\Http\Controllers\MCPController::class, 'testConnection']);
    
    // Model operations
    Route::get('/servers/{serverId}/models', [\App\Http\Controllers\MCPController::class, 'listModels']);
    Route::get('/servers/{serverId}/models/{modelId}', [\App\Http\Controllers\MCPController::class, 'getModel']);
    Route::post('/servers/{serverId}/models/{modelId}/complete', [\App\Http\Controllers\MCPController::class, 'complete']);
});

// AI Processing Routes
Route::prefix('ai')->group(function () {
    // Process any AI input
    Route::post('/process', [\App\Http\Controllers\AIController::class, 'process']);
    
    // Get available AI models and capabilities
    Route::get('/models', [\App\Http\Controllers\AIController::class, 'models']);
    
    // Test optimization service
    Route::post('/test-optimization', [\App\Http\Controllers\AIController::class, 'testOptimization']);
    
    // Voice processing routes
    Route::post('/voice/process', [VoiceController::class, 'processVoiceInput']);
    Route::post('/voice/command', [VoiceController::class, 'handleVoiceCommand']);
    Route::post('/voice/interrupt', [VoiceController::class, 'handleInterruption']);
});

// Deployment routes
Route::post('/projects/{id}/deploy', [DeploymentController::class, 'deploy']);
Route::post('/projects/{id}/deploy/{environment}', [DeploymentController::class, 'deployToEnvironment']);
Route::post('/projects/{id}/rollback', [DeploymentController::class, 'rollback']);
Route::get('/projects/{id}/environments', [DeploymentController::class, 'getEnvironments']);
Route::get('/projects/{id}/deployments/history', [DeploymentController::class, 'deploymentHistory']);
Route::post('/projects/{id}/domain', [DeploymentController::class, 'setupCustomDomain']);
Route::post('/projects/{id}/switch-deployment', [DeploymentController::class, 'switchDeployment']);

// Environment management routes
Route::post('/projects/{id}/environments/{env}/variables', [EnvironmentController::class, 'updateVariables']);
Route::post('/projects/{id}/environments/{env}/secrets', [EnvironmentController::class, 'updateSecrets']);
Route::post('/projects/{id}/environments/{env}/migrate', [EnvironmentController::class, 'runMigrations']);
Route::post('/projects/{id}/provision-test', [EnvironmentController::class, 'provisionTestEnvironment']);

// GitHub webhook handler
Route::post('/webhooks/github/{projectId}', [WebhookController::class, 'handleGitHubWebhook']);

// Infrastructure metrics
Route::get('/projects/{id}/metrics', [MetricsController::class, 'getProjectMetrics']);
Route::get('/projects/{id}/logs', [MetricsController::class, 'getProjectLogs']);


// Enhanced AI routes
Route::post('/ai/process-requirements', [EnhancedAiController::class, 'processRequirements']);
Route::post('/ai/provide-clarification', [EnhancedAiController::class, 'provideClarification']);
Route::post('/ai/generate-application', [EnhancedAiController::class, 'generateApplication']);
Route::post('/ai/optimize-code', [EnhancedAiController::class, 'optimizeCode']);