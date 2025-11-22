<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ConversationManager
{
    protected $sessionId;
    protected $maxHistoryLength = 10;

    public function __construct($sessionId = null)
    {
        $this->sessionId = $sessionId ?? session()->getId();
    }

    public function getContextHistory()
    {
        return Cache::get("conversation:{$this->sessionId}", []);
    }

    public function updateContext($newContext)
    {
        $history = $this->getContextHistory();
        
        // Add new context and maintain history length
        array_push($history, [
            'timestamp' => now(),
            'context' => $newContext
        ]);

        if (count($history) > $this->maxHistoryLength) {
            array_shift($history);
        }

        Cache::put("conversation:{$this->sessionId}", $history, 3600); // 1 hour
        return $history;
    }

    public function clearContext()
    {
        Cache::forget("conversation:{$this->sessionId}");
    }

    public function detectIntent($userInput)
    {
        try {
            $client = new Client();
            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                ],
                'json' => [
                    'model' => 'gpt-4',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "Analyze the user's intent. Classify into: CREATE_APP, MODIFY_APP, 
                            ADD_FEATURE, DEPLOY_APP, TECHNICAL_QUESTION, GENERAL_QUESTION. 
                            Consider context: " . json_encode($this->getContextHistory())
                        ],
                        [
                            'role' => 'user',
                            'content' => $userInput
                        ]
                    ]
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $this->parseIntent($result['choices'][0]['message']['content']);

        } catch (\Exception $e) {
            Log::error('Intent detection error: ' . $e->getMessage());
            return 'GENERAL_QUESTION';
        }
    }

    public function handleFollowUp($userInput, $previousContext)
    {
        // Handle follow-up questions with context awareness
        $context = $this->getContextHistory();
        
        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a conversational AI assistant. Maintain context from previous interactions: " . 
                                    json_encode($context) . ". Provide helpful, context-aware responses."
                    ],
                    [
                        'role' => 'user',
                        'content' => $userInput
                    ]
                ]
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}