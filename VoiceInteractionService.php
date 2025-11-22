<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VoiceInteractionService
{
    protected $client;
    protected $supportedLanguages = [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'it' => 'Italian',
        'pt' => 'Portuguese',
        'ru' => 'Russian',
        'zh' => 'Chinese',
        'ja' => 'Japanese',
        'ko' => 'Korean'
    ];

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false
        ]);
    }

    /**
     * Process voice input and return text transcription
     */
    public function processVoiceInput($audioFile, $language = 'en-US')
    {
        try {
            // Convert speech to text
            $transcribedText = $this->speechToText($audioFile, $language);
            
            // Process voice command
            $commandResult = $this->processVoiceCommand($transcribedText, $project);
            
            return [
                'success' => true,
                'transcribed_text' => $transcribedText,
                'command_result' => $commandResult,
                'audio_response' => $this->textToSpeech($commandResult['response'])
            ];

        } catch (\Exception $e) {
            Log::error('Voice processing error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function speechToText($audioFile)
    {
        try {
            $response = $this->client->post('https://api.openai.com/v1/audio/transcriptions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($audioFile, 'r'),
                        'filename' => 'voice_input.wav'
                    ],
                    [
                        'name' => 'model',
                        'contents' => 'whisper-1'
                    ],
                    [
                        'name' => 'response_format',
                        'contents' => 'text'
                    ]
                ]
            ]);

            return $response->getBody()->getContents();

        } catch (\Exception $e) {
            // Fallback to local speech recognition if available
            return $this->fallbackSpeechToText($audioFile);
        }
    }

    protected function processVoiceCommand($text, $project = null)
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
                        'content' => $this->getVoiceCommandSystemPrompt($project)
                    ],
                    [
                        'role' => 'user',
                        'content' => $text
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.3
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        $responseText = $result['choices'][0]['message']['content'];

        return [
            'action' => $this->extractAction($responseText),
            'parameters' => $this->extractParameters($responseText),
            'response' => $responseText
        ];
    }

    protected function getVoiceCommandSystemPrompt($project)
    {
        return "You are a voice command processor for ALICE Pro. Interpret voice commands and respond with:
        1. Action to take (create_project, modify_project, deploy, etc.)
        2. Parameters needed for the action
        3. Clear confirmation response
        
        Current project: " . ($project ? $project->name : 'None') . "
        Available actions: create, deploy, modify, test, export";
    }
}