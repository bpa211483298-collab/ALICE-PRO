<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AIOrchestrationService
{
    protected $client;
    protected $availableModels = [];
    protected $modelCapabilities = [];
    protected $defaultModel = 'openai/gpt-4';
    protected $optimizationService;
    
    public function __construct(AIOptimizationService $optimizationService)
    {
        $this->client = new Client([
            'timeout' => 60,
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
        
        $this->optimizationService = $optimizationService;
        $this->initializeModels();
    }
    
    // Model categories and their capabilities
    protected $modelCategories = [
        'reasoning' => [
            'models' => [
                'openai/gpt-4',
                'anthropic/claude-3-opus',
                'google/gemini-pro',
                'mistral/mixtral-8x7b',
                'cohere/command-r-plus'
            ],
            'description' => 'Complex reasoning, problem solving, and planning',
            'strengths' => ['logic', 'analysis', 'strategy', 'planning']
        ],
        'coding' => [
            'models' => [
                'openai/gpt-4',
                'anthropic/claude-3-sonnet',
                'deepseek/deepseek-coder',
                'bigcode/starcoder2',
                'codellama/codellama-70b'
            ],
            'description' => 'Code generation, completion, and debugging',
            'strengths' => ['code_generation', 'debugging', 'refactoring', 'documentation']
        ],
        'multimodal' => [
            'models' => [
                'openai/gpt-4-vision',
                'google/gemini-pro-vision',
                'anthropic/claude-3-opus',
                'qwen/qwen-vl',
                'llava/llava-1.5-13b'
            ],
            'description' => 'Image, video, and multimodal understanding',
            'strengths' => ['image_analysis', 'visual_qa', 'document_understanding']
        ],
        'multilingual' => [
            'models' => [
                'google/gemini-pro',
                'meta/nllb-200-3.3b',
                'facebook/m2m100-12b',
                'microsoft/xtremedistil-l6-h256-en-de-fr-es-it-zh',
                'nvidia/nemotron-4-340b'
            ],
            'description' => 'Multilingual text processing and translation',
            'strengths' => ['translation', 'multilingual_understanding', 'localization']
        ],
        'creative' => [
            'models' => [
                'anthropic/claude-3-opus',
                'openai/gpt-4',
                'google/gemini-pro',
                'cohere/command-nightly',
                'mistral/mixtral-8x7b-instruct'
            ],
            'description' => 'Creative writing and content generation',
            'strengths' => ['storytelling', 'copywriting', 'ideation', 'brainstorming']
        ],
        'specialized' => [
            'models' => [
                'deepmind/alphafold',
                'openai/whisper',
                'stabilityai/stable-diffusion-xl',
                'eleutherai/pythia-12b',
                'cerebras/btlm-3b-8k'
            ],
            'description' => 'Specialized tasks like protein folding, speech, etc.',
            'strengths' => ['specialized_tasks', 'domain_specific']
        ]
    ];

    protected function initializeModels(): void
    {
        foreach ($this->modelCategories as $category => $data) {
            foreach ($data['models'] as $model) {
                if (!isset($this->availableModels[$model])) {
                    $this->availableModels[$model] = [];
                }
                $this->availableModels[$model]['categories'][] = $category;
                $this->availableModels[$model]['strengths'] = array_merge(
                    $this->availableModels[$model]['strengths'] ?? [],
                    $data['strengths']
                );
            }
        }
    }
    
    /**
     * Process input with optimization and route to appropriate model
     */
    public function processInput(
        string $input, 
        array $context = [], 
        string $inputType = 'text',
        array $requirements = []
    ): array {
        // Optimize input based on task type
        $taskType = $this->determineTaskType($input, $context);
        $optimized = $this->optimizationService->optimizeInput($input, $taskType, $context);
        
        // Analyze optimized input to determine requirements
        $analysis = $this->analyzeInput($optimized['optimized_prompt'], $inputType, $optimized['enhanced_context']);
        
        // Select the most suitable model based on analysis
        $selectedModel = $this->selectModel($analysis, $requirements);
        
        // Process the input with the selected model
        $rawResponse = $this->callModel($selectedModel, $optimized['optimized_prompt'], $analysis);
        
        // Format the output for better user experience
        return $this->optimizationService->formatOutput(
            $rawResponse,
            $this->getOutputFormat($taskType)
        );
    }
    
    /**
     * Analyze input to determine requirements
     */
    protected function analyzeInput(string $input, string $inputType, array $context = []): array
    {
        $analysis = [
            'language' => $this->detectLanguage($input),
            'complexity' => $this->assessComplexity($input),
            'intent' => $this->determineIntent($input, $context),
            'input_type' => $inputType,
            'tokens' => $this->countTokens($input),
            'requires_vision' => $inputType === 'image',
            'requires_code' => $this->containsCode($input),
            'requires_reasoning' => $this->requiresReasoning($input, $context)
        ];
        
        return $analysis;
    }
    
    /**
     * Select the most appropriate model based on analysis
     */
    protected function selectModel(array $analysis, array $requirements = []): string
    {
        // Apply requirements if specified
        if (!empty($requirements['model'])) {
            if (isset($this->availableModels[$requirements['model']])) {
                return $requirements['model'];
            }
        }
        
        // Select model based on input type and requirements
        if ($analysis['requires_vision']) {
            return $this->selectBestModel(['multimodal'], $analysis, $requirements);
        }
        
        if ($analysis['requires_code']) {
            return $this->selectBestModel(['coding'], $analysis, $requirements);
        }
        
        if ($analysis['requires_reasoning'] || $analysis['complexity'] > 7) {
            return $this->selectBestModel(['reasoning'], $analysis, $requirements);
        }
        
        if ($analysis['language'] !== 'en') {
            return $this->selectBestModel(['multilingual'], $analysis, $requirements);
        }
        
        // Default to general model
        return $this->defaultModel;
    }
    
    /**
     * Select the best model from specified categories
     */
    protected function selectBestModel(array $categories, array $analysis, array $requirements = []): string
    {
        $candidateModels = [];
        
        // Get all models in the specified categories
        foreach ($categories as $category) {
            if (isset($this->modelCategories[$category])) {
                foreach ($this->modelCategories[$category]['models'] as $model) {
                    $candidateModels[$model] = 0;
                    
                    // Score based on category match
                    $candidateModels[$model] += 5;
                    
                    // Adjust score based on language support
                    if ($analysis['language'] !== 'en') {
                        if ($this->supportsLanguage($model, $analysis['language'])) {
                            $candidateModels[$model] += 3;
                        }
                    }
                    
                    // Adjust score based on complexity
                    if ($analysis['complexity'] > 7 && in_array('reasoning', $this->availableModels[$model]['strengths'] ?? [])) {
                        $candidateModels[$model] += 2;
                    }
                }
            }
        }
        
        // Sort by score and return the best model
        arsort($candidateModels);
        return array_key_first($candidateModels) ?: $this->defaultModel;
    }
    
    /**
     * Prepare the prompt with context and requirements
     */
    protected function preparePrompt(
        string $input, 
        array $context, 
        array $requirements,
        array $analysis
    ): array {
        $prompt = [
            'role' => 'user',
            'content' => $input
        ];
        
        // Add system message with context and requirements
        $systemMessage = [
            'role' => 'system',
            'content' => 'You are a helpful AI assistant. ' . 
                'Please respond to the user\'s request based on the following context and requirements.\n\n' .
                (!empty($context) ? "CONTEXT:\n" . json_encode($context, JSON_PRETTY_PRINT) . "\n\n" : '') .
                (!empty($requirements) ? "REQUIREMENTS:\n" . json_encode($requirements, JSON_PRETTY_PRINT) . "\n\n" : '') .
                "LANGUAGE: {$analysis['language']}\n" .
                "COMPLEXITY: {$analysis['complexity']}/10\n" .
                "INTENT: {$analysis['intent']}"
        ];
        
        return [$systemMessage, $prompt];
    }
    
    /**
     * Call the selected model with the prepared prompt
     */
    protected function callModel(string $model, array $messages, array $analysis)
    {
        try {
            // This is a simplified example - in a real implementation, you would:
            // 1. Format the request according to the model's API
            // 2. Handle rate limiting and retries
            // 3. Process the response
            
            $endpoint = $this->getModelEndpoint($model);
            
            $response = $this->client->post($endpoint, [
                'json' => [
                    'model' => $model,
                    'messages' => $messages,
                    'max_tokens' => $analysis['tokens'] * 2, // Allow for longer responses
                    'temperature' => 0.7,
                    'top_p' => 0.9,
                ]
            ]);
            
            $result = json_decode($response->getBody()->getContents(), true);
            
            return [
                'model' => $model,
                'content' => $result['choices'][0]['message']['content'] ?? '',
                'usage' => $result['usage'] ?? [],
                'analysis' => $analysis
            ];
            
        } catch (\Exception $e) {
            Log::error("Error calling model {$model}: " . $e->getMessage());
            
            // Fallback to default model if the selected one fails
            if ($model !== $this->defaultModel) {
                return $this->callModel($this->defaultModel, $messages, $analysis);
            }
            
            throw new \Exception('Failed to process request with any available model');
        }
    }
    
    /**
     * Post-process the result before returning
     */
    protected function postProcessResult(array $result, array $analysis): array
    {
        // Apply any necessary post-processing based on the analysis
        // For example, translation, formatting, etc.
        
        return $result;
    }
    
    protected function determineTaskType(string $input, array $context): string
    {
        // Simple task type detection - can be enhanced with ML
        $input = strtolower($input);
        
        if (preg_match('/\b(?:code|function|class|import|require)\b/', $input)) {
            return 'code_generation';
        }
        
        if (preg_match('/\b(?:how|what|when|where|why|who|explain|tell me about)\b/', $input)) {
            return 'qa';
        }
        
        if (preg_match('/\b(?:write|create|generate|compose|draft|summarize)\b/', $input)) {
            return 'content_creation';
        }
        
        if (preg_match('/\b(?:analyze|compare|evaluate|find|show|list|calculate)\b/', $input)) {
            return 'analytical';
        }
        
        return 'general';
    }
    
    protected function getOutputFormat(string $taskType): string
    {
        $formats = [
            'code_generation' => 'code',
            'qa' => 'structured',
            'content_creation' => 'narrative',
            'analytical' => 'analytical',
            'default' => 'default'
        ];
        
        return $formats[$taskType] ?? $formats['default'];
    }
    
    // Helper methods for input analysis
    
    protected function detectLanguage(string $text): string
    {
        // In a real implementation, use a language detection library
        return 'en'; // Default to English
    }
    
    protected function assessComplexity(string $text): int
    {
        // Simple complexity assessment based on text length and features
        $score = 0;
        $wordCount = str_word_count($text);
        
        if ($wordCount > 100) $score += 3;
        elseif ($wordCount > 50) $score += 2;
        else $score += 1;
        
        // Check for complex patterns
        if (preg_match('/\b(?:analyze|compare|evaluate|explain|justify|synthesize)\b/i', $text)) {
            $score += 2;
        }
        
        if (preg_match('/\b(?:why|how|what if|consider|implications?)\b/i', $text)) {
            $score += 2;
        }
        
        return min(10, $score);
    }
    
    protected function determineIntent(string $text, array $context = []): string
    {
        // Simple intent detection
        $text = strtolower($text);
        
        if (preg_match('/\b(?:how|why|what|when|where|who|explain|describe|tell me about)\b/', $text)) {
            return 'information';
        }
        
        if (preg_match('/\b(?:create|write|generate|make|build|code)\b/', $text)) {
            return 'generation';
        }
        
        if (preg_match('/\b(?:fix|debug|error|problem|issue|not working)\b/', $text)) {
            return 'debugging';
        }
        
        if (preg_match('/\b(?:translate|language|in \w+)\b/', $text)) {
            return 'translation';
        }
        
        return 'general';
    }
    
    protected function countTokens(string $text): int
    {
        // Rough estimate: 1 token ≈ 4 characters
        return (int) ceil(strlen($text) / 4);
    }
    
    protected function containsCode(string $text): bool
    {
        return preg_match('/\b(?:function|class|def|import|require|\$[a-z]|\/\/|#|\/\*|\*\/|\{|\}|\[|\]|\(|\)|;|=|\+|-|\*|\/|<|>|\|)/i', $text);
    }
    
    protected function requiresReasoning(string $text, array $context = []): bool
    {
        $text = strtolower($text);
        
        return (bool) preg_match('/\b(?:analy[sz]e|compare|contrast|evaluate|justify|synthesize|reason|logic|think(?:ing)?|consider|implications?|conclusion|therefore|thus|hence|because|since|as a result|consequently|accordingly|due to|in order to|so that|in that case|in this case|given that|provided that|seeing that|in view of|in light of|on the grounds that|for the reason that|on account of|owing to|as|for)\b/', $text);
    }
    
    protected function supportsLanguage(string $model, string $language): bool
    {
        // In a real implementation, check the model's language support
        return true; // Assume all models support all languages for this example
    }
    
    protected function getModelEndpoint(string $model): string
    {
        // In a real implementation, return the appropriate API endpoint for the model
        return 'https://api.openai.com/v1/chat/completions'; // Default to OpenAI-compatible endpoint
    }
    
    /**
     * Get information about available models
     */
    public function getAvailableModels(): array
    {
        return [
            'models' => array_keys($this->availableModels),
            'categories' => array_map(function($category) {
                return [
                    'name' => $category,
                    'description' => $this->modelCategories[$category]['description'] ?? '',
                    'models' => $this->modelCategories[$category]['models'] ?? []
                ];
            }, array_keys($this->modelCategories))
        ];
    }
    
    /**
     * Process multiple inputs in parallel
     */
    public function batchProcess(array $inputs, array $options = []): array
    {
        $promises = [];
        
        foreach ($inputs as $index => $input) {
            $promises[$index] = $this->processInputAsync(
                $input['text'] ?? '',
                $input['context'] ?? [],
                $input['type'] ?? 'text',
                $input['requirements'] ?? []
            );
        }
        
        $results = [];
        foreach (Promise\Utils::settle($promises)->wait() as $index => $promise) {
            if ($promise['state'] === 'fulfilled') {
                $results[$index] = $promise['value'];
            } else {
                $results[$index] = [
                    'error' => $promise['reason']->getMessage(),
                    'model' => 'error'
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Process input asynchronously
     */
    protected function processInputAsync(
        string $input, 
        array $context = [], 
        string $inputType = 'text',
        array $requirements = []
    ): \GuzzleHttp\Promise\PromiseInterface {
        return $this->client->postAsync('async/process', [
            'json' => [
                'input' => $input,
                'context' => $context,
                'input_type' => $inputType,
                'requirements' => $requirements
            ]
        ])->then(
            function ($response) {
                return json_decode($response->getBody()->getContents(), true);
            },
            function ($e) {
                Log::error('Async processing error: ' . $e->getMessage());
                throw $e;
            }
        );
    }
}
