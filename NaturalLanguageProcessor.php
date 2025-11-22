<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class NaturalLanguageProcessor
{
    protected $client;
    protected $contextHistory = [];

    public function __construct()
    {
        $this->client = new Client();
    }

    public function parseAppDescription($userInput, $conversationHistory = [])
    {
        try {
            $this->contextHistory = $conversationHistory;

            // Step 1: Analyze and clarify requirements
            $analysis = $this->analyzeRequirements($userInput);
            
            // Step 2: Extract technical specifications
            $technicalSpecs = $this->extractTechnicalSpecs($analysis['clarified_requirements']);
            
            // Step 3: Identify ambiguous elements
            $ambiguities = $this->identifyAmbiguities($technicalSpecs);
            
            // Step 4: Generate intelligent suggestions
            $suggestions = $this->generateSuggestions($technicalSpecs, $ambiguities);
            
            // Step 5: Create complete technical requirements
            $completeRequirements = $this->createCompleteRequirements($technicalSpecs, $suggestions);

            return [
                'original_input' => $userInput,
                'analysis' => $analysis,
                'technical_specifications' => $technicalSpecs,
                'identified_ambiguities' => $ambiguities,
                'ai_suggestions' => $suggestions,
                'complete_requirements' => $completeRequirements,
                'conversation_context' => $this->updateContext($completeRequirements)
            ];

        } catch (\Exception $e) {
            Log::error('NLP Processing Error: ' . $e->getMessage());
            return $this->fallbackProcessing($userInput);
        }
    }

    protected function analyzeRequirements($userInput)
    {
        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getAnalysisSystemPrompt()
                    ],
                    [
                        'role' => 'user',
                        'content' => $userInput
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.3
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        return $this->parseAnalysisResponse($result['choices'][0]['message']['content']);
    }

    protected function getAnalysisSystemPrompt()
    {
        return "You are a senior business analyst and technical architect. Analyze the app description and:

        1. Parse both technical and non-technical language equally
        2. Identify core features and requirements
        3. Detect ambiguous or missing information
        4. Consider context from previous interactions: " . json_encode($this->contextHistory) . "
        5. Recognize intent behind feature requests
        6. Categorize requirements into: MUST HAVE, SHOULD HAVE, COULD HAVE
        7. Provide clarity on vague requirements

        Return JSON with: clarified_requirements, feature_categories, detected_intents, missing_information";
    }

    protected function extractTechnicalSpecs($clarifiedRequirements)
    {
        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "Convert business requirements into technical specifications. Output JSON with: 
                        frontend_components, backend_apis, database_schema, third_party_integrations, 
                        security_requirements, performance_targets, accessibility_requirements, seo_requirements"
                    ],
                    [
                        'role' => 'user',
                        'content' => $clarifiedRequirements
                    ]
                ]
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function identifyAmbiguities($technicalSpecs)
    {
        // AI-powered ambiguity detection
        $ambiguityPrompt = "Identify ambiguous, incomplete, or potentially problematic areas in these technical specs: " . 
                          json_encode($technicalSpecs);

        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a quality assurance engineer. Identify ambiguous, incomplete, or problematic requirements."
                    ],
                    [
                        'role' => 'user',
                        'content' => $ambiguityPrompt
                    ]
                ]
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function generateSuggestions($technicalSpecs, $ambiguities)
    {
        // Generate intelligent improvements (users never see this)
        $suggestionPrompt = "Technical specs: " . json_encode($technicalSpecs) . 
                           " Ambiguities: " . json_encode($ambiguities) . 
                           " Suggest improvements and optimizations.";

        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a senior architect. Suggest technical improvements, optimizations, and best practices. Be comprehensive but concise."
                    ],
                    [
                        'role' => 'user',
                        'content' => $suggestionPrompt
                    ]
                ]
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}