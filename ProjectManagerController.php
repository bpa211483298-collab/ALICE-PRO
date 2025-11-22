<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\RealTimeCodeEngine;
use App\Services\InstantDeploymentService;
use App\Services\DatabaseIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectManagerController extends Controller
{
    protected $codeEngine;
    protected $deploymentService;
    protected $databaseService;

    public function __construct(
        RealTimeCodeEngine $codeEngine,
        InstantDeploymentService $deploymentService,
        DatabaseIntegrationService $databaseService
    ) {
        $this->codeEngine = $codeEngine;
        $this->deploymentService = $deploymentService;
        $this->databaseService = $databaseService;
    }

    public function createProject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:web_app,mobile_app,website,game,ebook',
            'prompt' => 'required|string',
            'voice_input' => 'sometimes|file|mimes:mp3,wav,m4a'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $user = $request->user();

            // Create project record
            $project = Project::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'ai_prompt' => $request->prompt,
                'status' => 'initializing',
                'build_logs' => ['Project created successfully']
            ]);

            // Start real-time code generation (async)
            dispatch(function () use ($project) {
                $this->codeEngine->generateCompleteApplication($project);
            })->onQueue('code-generation');

            return response()->json([
                'success' => true,
                'project' => $project,
                'message' => 'Project creation started. You will be notified when completed.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Project creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deployProject(Request $request, $projectId)
    {
        try {
            $project = Project::where('_id', $projectId)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            if ($project->status !== 'completed') {
                return response()->json([
                    'error' => 'Project must be completed before deployment'
                ], 400);
            }

            // Start deployment process
            $deploymentResult = $this->deploymentService->deployToRender($project);

            // Update project with deployment info
            $project->update([
                'deployment_url' => $deploymentResult['url'],
                'status' => 'deployed',
                'build_logs' => array_merge($project->build_logs ?? [], [
                    'Deployment started: ' . $deploymentResult['deployment_id']
                ])
            ]);

            return response()->json([
                'success' => true,
                'deployment' => $deploymentResult,
                'message' => 'Deployment started successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Deployment failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProjectDashboard(Request $request)
    {
        $user = $request->user();
        
        $projects = Project::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total_projects' => $projects->count(),
            'completed_projects' => $projects->where('status', 'completed')->count(),
            'deployed_projects' => $projects->where('status', 'deployed')->count(),
            'active_deployments' => $projects->where('status', 'deployed')->count()
        ];

        return response()->json([
            'stats' => $stats,
            'recent_projects' => $projects->take(5),
            'active_deployments' => $projects->where('status', 'deployed')->take(3)
        ]);
    }
}