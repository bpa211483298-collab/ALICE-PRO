<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\AiService;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiController extends Controller
{
    protected $aiService;
    protected $codeGenerator;

    public function __construct(AiService $aiService, CodeGeneratorService $codeGenerator)
    {
        $this->aiService = $aiService;
        $this->codeGenerator = $codeGenerator;
    }

    public function generateProject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:web_app,mobile_app,website,game,ebook',
            'prompt' => 'required|string',
            'is_public' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $user = $request->user();
            
            // Generate code using AI
            $aiResponse = $this->aiService->generateCode(
                $request->prompt, 
                $request->type
            );

            // Create project
            $project = Project::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'status' => 'generated',
                'ai_prompt' => $request->prompt,
                'generated_code' => $aiResponse['code'] ?? [],
                'file_structure' => $aiResponse['file_structure'] ?? [],
                'dependencies' => $aiResponse['dependencies'] ?? [],
                'ai_models_used' => ['openrouter/gpt-3.5-turbo'],
                'is_public' => $request->is_public ?? false,
                'build_logs' => ['AI generation completed successfully']
            ]);

            // Generate project files
            $zipPath = $this->codeGenerator->generateProjectFiles($project);

            return response()->json([
                'success' => true,
                'project' => $project,
                'download_url' => url("api/projects/{$project->id}/download")
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Project generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processVoiceCommand(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'audio' => 'required|file|mimes:mp3,wav,m4a|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $audioPath = $request->file('audio')->store('voice_commands');
            $transcribedText = $this->aiService->processVoiceCommand($audioPath);

            return response()->json([
                'success' => true,
                'transcribed_text' => $transcribedText,
                'suggested_prompt' => $transcribedText // In real implementation, this would be processed
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Voice command processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
}