<?php

namespace App\Http\Controllers;

use App\Facades\AI;
use Illuminate\Http\Request;

class AIController extends Controller
{
    /**
     * Process user input using the AI service
     */
    public function process(Request $request)
    {
        $input = $request->input('input');
        $context = $request->input('context', []);
        $inputType = $request->input('type', 'text');
        
        try {
            $result = AI::process($input, $context, $inputType);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : []
            ], 500);
        }
    }
    
    /**
     * Get information about available AI models
     */
    public function models()
    {
        return response()->json([
            'models' => config('ai.providers'),
            'default' => config('ai.default'),
            'capabilities' => config('ai.default_capabilities')
        ]);
    }
    
    /**
     * Test the AI optimization service
     */
    public function testOptimization(Request $request)
    {
        $input = $request->input('input', 'How do I create a new Laravel project?');
        $taskType = $request->input('task_type', 'qa');
        $context = $request->input('context', []);
        
        $optimized = AI::optimizeInput($input, $taskType, $context);
        
        return response()->json([
            'input' => $input,
            'task_type' => $taskType,
            'optimized' => $optimized
        ]);
    }
}
