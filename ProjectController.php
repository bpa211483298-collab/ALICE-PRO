<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $projects = Project::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['projects' => $projects]);
    }

    public function show(Request $request, $id)
    {
        $project = Project::where('_id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json(['project' => $project]);
    }

    public function download(Request $request, $id)
    {
        $project = Project::where('_id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $zipPath = storage_path("app/projects/{$project->name}.zip");
        
        if (!file_exists($zipPath)) {
            return response()->json(['error' => 'Project files not found'], 404);
        }

        return response()->download($zipPath, "{$project->name}.zip");
    }

    public function destroy(Request $request, $id)
    {
        $project = Project::where('_id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Delete associated files
        $zipPath = storage_path("app/projects/{$project->name}.zip");
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }
}