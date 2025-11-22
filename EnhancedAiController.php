<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\NaturalLanguageProcessor;
use App\Services\EnhancedCodeEngine;
use App\Services\ConversationManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EnhancedAiController extends Controller
{
    protected $nlpProcessor;
    protected $codeEngine;
    protected $conversationManager;

    public function __construct(
        NaturalLanguageProcessor $nlpProcessor,
        EnhancedCodeEngine $codeEngine,
        ConversationManager $conversationManager
    ) {
        $this->nlpProcessor = $nlpProcessor;
        $this->codeEngine = $codeEngine;
        $this->conversationManager = $conversationManager;
    }

    public function processAppDescription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|min:10',
            'project_type' => 'required|in:web_app,mobile_app,website,game,ebook'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            // Process with advanced NLP
            $nlpResult = $this->nlpProcessor->parseAppDescription(
                $request->description,
                $this->conversationManager->getContextHistory()
            );

            // Update conversation context
            $this->conversationManager->updateContext($nlpResult);

            return response()->json([
                'success' => true,
                'clarified_requirements' => $nlpResult['analysis']['clarified_requirements'],
                'technical_specifications' => $nlpResult['technical_specifications'],
                'complete_requirements' => $nlpResult['complete_requirements'],
                'conversation_context' => $nlpResult['conversation_context']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'NLP processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateOptimizedApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requirements' => 'required|array',
            'project_type' => 'required|in:web_app,mobile_app,website,game,ebook'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            // This would create a project and start the enhanced generation process
            $project = $this->codeEngine->createProjectFromRequirements(
                $request->requirements,
                $request->project_type,
                $request->user()
            );

            return response()->json([
                'success' => true,
                'project_id' => $project->id,
                'message' => 'Optimized application generation started'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Application generation failed: ' . $e->getMessage()
            ], 500);
        }
    }
}

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AdvancedCodeGenerationService;
use App\Services\EnhancedNaturalLanguageProcessor;
use App\Services\AdvancedCodeOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EnhancedAiController extends Controller
{
    protected $codeGenerationService;
    protected $nlpProcessor;
    protected $optimizationService;

    public function __construct(
        AdvancedCodeGenerationService $codeGenerationService,
        EnhancedNaturalLanguageProcessor $nlpProcessor,
        AdvancedCodeOptimizationService $optimizationService
    ) {
        $this->codeGenerationService = $codeGenerationService;
        $this->nlpProcessor = $nlpProcessor;
        $this->optimizationService = $optimizationService;
    }

    public function processRequirements(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requirements' => 'required|string|min:10',
            'conversation_context' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $result = $this->nlpProcessor->processRequirements(
                $request->requirements,
                $request->conversation_context ?? []
            );

            return response()->json([
                'success' => true,
                'parsed_requirements' => $result['parsed_requirements'],
                'clarification_questions' => $result['clarification_questions'],
                'technical_specifications' => $result['technical_specifications'],
                'intent_analysis' => $result['intent_analysis'],
                'ai_suggestions' => $result['ai_suggestions'],
                'conversation_context' => $result['conversation_context']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Requirements processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function provideClarification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|string',
            'answer' => 'required|string',
            'conversation_context' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $result = $this->nlpProcessor->handleClarifyingAnswer(
                $request->question_id,
                $request->answer,
                $request->conversation_context
            );

            return response()->json([
                'success' => true,
                'updated_requirements' => $result['updated_requirements'],
                'conversation_context' => $result['conversation_context']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Clarification processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requirements' => 'required|array',
            'preferences' => 'sometimes|array',
            'conversation_context' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $result = $this->codeGenerationService->generateCompleteApplication(
                $request->requirements,
                $request->preferences ?? []
            );

            return response()->json([
                'success' => true,
                'tech_stack' => $result['tech_stack'],
                'database_schema' => $result['database_schema'],
                'backend_code' => $result['backend_code'],
                'frontend_code' => $result['frontend_code'],
                'infrastructure' => $result['infrastructure'],
                'deployment_config' => $result['deployment_config']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Application generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function optimizeCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'backend_code' => 'required|array',
            'frontend_code' => 'required|array',
            'infrastructure' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $result = $this->optimizationService->optimizeCode(
                $request->backend_code,
                $request->frontend_code,
                $request->infrastructure
            );

            return response()->json([
                'success' => true,
                'optimized_backend' => $result['backend'],
                'optimized_frontend' => $result['frontend'],
                'optimized_infrastructure' => $result['infrastructure']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Code optimization failed: ' . $e->getMessage()
            ], 500);
        }
    }
}