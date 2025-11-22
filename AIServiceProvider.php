<?php

namespace App\Providers;

use App\Services\AIOrchestrationService;
use App\Services\AIOptimizationService;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/ai.php', 'ai'
        );

        // Register AI Optimization Service
        $this->app->singleton(AIOptimizationService::class, function ($app) {
            return new AIOptimizationService();
        });

        // Register AI Orchestration Service
        $this->app->singleton(AIOrchestrationService::class, function ($app) {
            return new AIOrchestrationService(
                $app->make(AIOptimizationService::class)
            );
        });

        // Register AI Facade
        $this->app->singleton('ai', function ($app) {
            return $app->make(AIOrchestrationService::class);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/ai.php' => config_path('ai.php'),
        ], 'config');
    }
}
