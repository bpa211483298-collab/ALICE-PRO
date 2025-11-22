<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class EnhancedNaturalLanguageProcessor
{
    protected $client;
    protected $conversationHistory = [];

    public function __construct()
    {
        $this->client = new Client();
    }

    public function processRequirements($userInput, $conversationContext = [])
    {
        try {
            $this->conversationHistory = $conversationContext;

            // Step 1: Parse and understand requirements
            $parsedRequirements = $this->parseRequirements($userInput);
            
            // Step 2: Identify ambiguities and generate clarifying questions
            $clarifications = $this->identifyAmbiguities($parsedRequirements);
            
            // Step 3: Extract technical specifications
            $technicalSpecs = $this->extractTechnicalSpecs($parsedRequirements);
            
            // Step 4: Recognize intent and features
            $intentAnalysis = $this->analyzeIntent($parsedRequirements);
            
            // Step 5: Generate smart suggestions
            $suggestions = $this->generateSuggestions($parsedRequirements, $technicalSpecs);
            
            // Update conversation history
            $this->updateConversationHistory($parsedRequirements, $technicalSpecs);

            return [
                'parsed_requirements' => $parsedRequirements,
                'clarification_questions' => $clarifications,
                'technical_specifications' => $technicalSpecs,
                'intent_analysis' => $intentAnalysis,
                'ai_suggestions' => $suggestions,
                'conversation_context' => $this->conversationHistory
            ];

        } catch (\Exception $e) {
            Log::error('NLP processing error: ' . $e->getMessage());
            throw new \Exception('Requirements processing failed: ' . $e->getMessage());
        }
    }

    protected function parseRequirements($userInput)
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
                        'content' => "You are a requirements analyst. Parse and structure application requirements from natural language. 
                        Consider conversation history: " . json_encode($this->conversationHistory) . "
                        Return structured JSON with: features, user_stories, data_models, user_roles, technical_requirements."
                    ],
                    [
                        'role' => 'user',
                        'content' => $userInput
                    ]
                ],
                'max_tokens' => 3000,
                'temperature' => 0.3
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        return json_decode($result['choices'][0]['message']['content'], true);
    }

    protected function identifyAmbiguities($parsedRequirements)
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
                        'content' => "You are a business analyst. Identify ambiguous or incomplete requirements and generate clarifying questions."
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode($parsedRequirements)
                    ]
                ],
                'max_tokens' => 1500,
                'temperature' => 0.3
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        return json_decode($result['choices'][0]['message']['content'], true);
    }

    protected function extractTechnicalSpecs($parsedRequirements)
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
                        'content' => "You are a technical architect. Extract technical specifications from business requirements."
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode($parsedRequirements)
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.2
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        return json_decode($result['choices'][0]['message']['content'], true);
    }

    protected function analyzeIntent($parsedRequirements)
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
                        'content' => "You are an intent recognition system. Analyze the underlying intent and priority of features."
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode($parsedRequirements)
                    ]
                ],
                'max_tokens' => 1000,
                'temperature' => 0.2
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        return json_decode($result['choices'][0]['message']['content'], true);
    }

    protected function generateSuggestions($parsedRequirements, $technicalSpecs)
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
                        'content' => "You are a product strategist. Suggest improvements, optimizations, and additional features."
                    ],
                    [
                        'role' => 'user',
                        'content' => "Requirements: " . json_encode($parsedRequirements) . 
                                    " Technical Specs: " . json_encode($technicalSpecs)
                    ]
                ],
                'max_tokens' => 1500,
                'temperature' => 0.3
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        return json_decode($result['choices'][0]['message']['content'], true);
    }

    protected function updateConversationHistory($parsedRequirements, $technicalSpecs)
    {
        // Keep only the last 10 interactions for context
        if (count($this->conversationHistory) >= 10) {
            array_shift($this->conversationHistory);
        }

        $this->conversationHistory[] = [
            'timestamp' => now(),
            'requirements' => $parsedRequirements,
            'technical_specs' => $technicalSpecs
        ];
    }

    public function handleClarifyingAnswer($questionId, $answer, $conversationContext)
    {
        // Process user's answer to a clarifying question
        $this->conversationHistory = $conversationContext;
        
        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a requirements analyst. Integrate the user's clarification into the requirements."
                    ],
                    [
                        'role' => 'user',
                        'content' => "Question ID: " . $questionId . " Answer: " . $answer . 
                                    " Conversation Context: " . json_encode($this->conversationHistory)
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.3
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        $updatedRequirements = json_decode($result['choices'][0]['message']['content'], true);
        
        // Update conversation history with clarified requirements
        $this->updateConversationHistory($updatedRequirements, []);
        
        return [
            'updated_requirements' => $updatedRequirements,
            'conversation_context' => $this->conversationHistory
        ];
    }
}