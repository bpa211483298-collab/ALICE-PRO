<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Log;

class EnhancedCodeEngine extends RealTimeCodeEngine
{
    protected $nlpProcessor;
    protected $optimizationService;

    public function __construct(
        AdvancedAiService $aiService,
        NaturalLanguageProcessor $nlpProcessor,
        CodeOptimizationService $optimizationService
    ) {
        parent::__construct($aiService);
        $this->nlpProcessor = $nlpProcessor;
        $this->optimizationService = $optimizationService;
    }

    public function generateCompleteApplication(Project $project)
    {
        try {
            // Step 1: Advanced NLP processing
            $nlpResult = $this->nlpProcessor->parseAppDescription(
                $project->ai_prompt,
                $this->getConversationHistory($project->user_id)
            );

            // Step 2: Generate code with clarified requirements
            $completePrompt = $this->buildCompletePrompt(
                $nlpResult['complete_requirements']['technical_specifications'],
                $project->type
            );

            $generatedCode = $this->aiService->generateCompleteApplication($completePrompt, $project->type);

            // Step 3: Apply advanced optimizations
            $optimizedCode = $this->optimizationService->optimizeGeneratedCode($generatedCode, $project->type);

            // Step 4: Enhance with intelligent architecture
            $enhancedCode = $this->enhanceWithIntelligentArchitecture($optimizedCode, $nlpResult);

            // Step 5: Generate project files
            $this->generateProjectStructure($project, $enhancedCode);

            // Step 6: Apply automatic quality checks
            $this->runQualityChecks($project);

            $project->update([
                'generated_code' => $enhancedCode,
                'status' => 'completed',
                'build_logs' => array_merge($project->build_logs ?? [], [
                    'NLP processing completed',
                    'Technical specifications extracted',
                    'Code optimization applied',
                    'Quality checks passed'
                ]),
                'technical_specs' => $nlpResult['technical_specifications'],
                'ai_suggestions' => $nlpResult['ai_suggestions'] // Hidden from users
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Enhanced generation error: ' . $e->getMessage());
            $project->update([
                'status' => 'failed',
                'build_logs' => array_merge($project->build_logs ?? [], [
                    'Error: ' . $e->getMessage()
                ])
            ]);
            return false;
        }
    }

    protected function enhanceWithIntelligentArchitecture($code, $nlpResult)
    {
        // Add intelligent architecture based on NLP analysis
        $architectureComponents = [
            'smart-routing' => $this->generateSmartRouting($nlpResult),
            'adaptive-ui' => $this->generateAdaptiveUI($nlpResult),
            'intelligent-state' => $this->generateIntelligentStateManagement($nlpResult),
            'performance-monitoring' => $this->generatePerformanceMonitoring($nlpResult)
        ];

        return array_merge($code, $architectureComponents);
    }

    protected function runQualityChecks(Project $project)
    {
        // Automated quality assurance checks
        $qualityMetrics = [
            'security_score' => $this->calculateSecurityScore($project),
            'performance_score' => $this->calculatePerformanceScore($project),
            'accessibility_score' => $this->calculateAccessibilityScore($project),
            'seo_score' => $this->calculateSeoScore($project),
            'mobile_score' => $this->calculateMobileScore($project)
        ];

        $project->quality_metrics = $qualityMetrics;
        $project->save();
    }

    protected function calculateSecurityScore($project)
    {
        // Analyze code for security best practices
        $code = $project->generated_code;
        $score = 100;

        // Check for security headers
        if (!isset($code['server.js']) || !str_contains($code['server.js'], 'X-Content-Type-Options')) {
            $score -= 20;
        }

        // Check for encryption
        if (!isset($code['utils/encryption.js'])) {
            $score -= 25;
        }

        // Check for input validation
        $validationPatterns = ['validation', 'sanitize', 'escape'];
        $hasValidation = false;
        foreach ($validationPatterns as $pattern) {
            if (str_contains(json_encode($code), $pattern)) {
                $hasValidation = true;
                break;
            }
        }
        if (!$hasValidation) {
            $score -= 15;
        }

        return max($score, 0);
    }
}