<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AiService
{
    protected $client;
    protected $providers;

    public function __construct()
    {
        $this->client = new Client();
        $this->providers = [
            'openai' => [
                'url' => 'https://api.openai.com/v1/chat/completions',
                'key' => env('OPENAI_API_KEY'),
            ],
            'openrouter' => [
                'url' => 'https://openrouter.ai/api/v1/chat/completions',
                'key' => env('OPENROUTER_API_KEY'),
            ]
        ];
    }

    public function generateCode($prompt, $projectType, $provider = 'openrouter')
    {
        try {
            $providerConfig = $this->providers[$provider] ?? $this->providers['openrouter'];
            
            $response = $this->client->post($providerConfig['url'], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $providerConfig['key'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->getSystemPrompt($projectType)
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => 4000,
                    'temperature' => 0.7
                ],
                'timeout' => 60
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            return $this->parseAiResponse($result['choices'][0]['message']['content'], $projectType);

        } catch (\Exception $e) {
            Log::error('AI Service Error: ' . $e->getMessage());
            throw new \Exception('AI generation failed: ' . $e->getMessage());
        }
    }

    protected function getSystemPrompt($projectType)
    {
        $prompts = [
            'web_app' => 'You are an expert React developer. Generate complete, working React code with JSX. Include all necessary components, hooks, and styling. Return valid JSON with code and file structure.',
            'mobile_app' => 'You are an expert React Native developer. Generate complete, working React Native code for both iOS and Android.',
            'website' => 'You are an expert web developer. Generate complete, responsive HTML/CSS/JavaScript websites.',
            'game' => 'You are an expert game developer. Generate complete web-based games using HTML5 Canvas or Three.js.',
            'ebook' => 'You are an expert content creator. Generate complete ebooks in HTML format with styling and navigation.'
        ];

        return $prompts[$projectType] ?? $prompts['web_app'];
    }

    protected function parseAiResponse($response, $projectType)
    {
        // Try to parse as JSON
        if ($json = json_decode($response, true)) {
            return $json;
        }

        // Fallback: extract JSON from markdown code blocks
        if (preg_match('/```json\s*(.*?)\s*```/s', $response, $matches)) {
            return json_decode($matches[1], true);
        }

        // Ultimate fallback: return as raw code
        return [
            'code' => $response,
            'file_structure' => ['main' => 'app.' . ($projectType === 'web_app' ? 'jsx' : 'html')],
            'instructions' => 'AI generated content'
        ];
    }

    public function processVoiceCommand($audioData)
    {
        // This would integrate with speech-to-text services
        // For now, we'll simulate voice processing
        return "Create a responsive website for my bakery business";
    }
}