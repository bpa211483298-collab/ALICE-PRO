<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EnhancedVoiceService
{
    protected $client;
    protected $supportedLanguages = [
        'en' => 'English',
        'es' => 'Spanish', 
        'fr' => 'French',
        'de' => 'German',
        'zh' => 'Mandarin'
    ];

    public function __construct()
    {
        $this->client = new Client();
    }

    public function processVoiceInput($audioData, $language = 'en', $context = null)
    {
        try {
            // Step 1: Audio preprocessing and noise cancellation
            $processedAudio = $this->preprocessAudio($audioData);
            
            // Step 2: Speech to text with multi-language support
            $transcribedText = $this->speechToText($processedAudio, $language);
            
            // Step 3: Command vs description classification
            $commandType = $this->classifyVoiceInput($transcribedText, $context);
            
            // Step 4: Context-aware processing
            $processedResult = $this->processWithContext($transcribedText, $commandType, $context);
            
            // Step 5: Generate appropriate response
            $voiceResponse = $this->generateVoiceResponse($processedResult, $language);

            return [
                'success' => true,
                'transcribed_text' => $transcribedText,
                'command_type' => $commandType,
                'processed_result' => $processedResult,
                'voice_response' => $voiceResponse,
                'audio_url' => $this->textToSpeech($voiceResponse, $language)
            ];

        } catch (\Exception $e) {
            Log::error('Voice processing error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Voice processing failed. Please try again.'
            ];
        }
    }

    protected function preprocessAudio($audioData)
    {
        // Apply noise reduction and audio enhancement
        // This would use Web Audio API on frontend and potentially server-side processing
        return $audioData; // In real implementation, this would process the audio
    }

    protected function speechToText($audioData, $language)
    {
        try {
            // Try Web Speech API first (handled on frontend), fallback to server-based
            if ($this->isWebSpeechAvailable()) {
                // Frontend will handle this, but we need a fallback
                return $this->fallbackSpeechToText($audioData, $language);
            }

            // Use OpenAI Whisper as primary server-side solution
            $response = $this->client->post('https://api.openai.com/v1/audio/transcriptions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($audioData, 'r'),
                        'filename' => 'voice_input.wav'
                    ],
                    [
                        'name' => 'model',
                        'contents' => 'whisper-1'
                    ],
                    [
                        'name' => 'language',
                        'contents' => $language
                    ],
                    [
                        'name' => 'response_format',
                        'contents' => 'text'
                    ]
                ]
            ]);

            return $response->getBody()->getContents();

        } catch (\Exception $e) {
            // Fallback to Google Speech-to-Text
            return $this->googleSpeechToText($audioData, $language);
        }
    }

    protected function classifyVoiceInput($text, $context)
    {
        // Determine if input is a command or description
        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "Classify this voice input as either 'command' or 'description'. 
                        Consider context: " . json_encode($context) . "
                        Commands are instructions for the platform. Descriptions are for app creation."
                    ],
                    [
                        'role' => 'user',
                        'content' => $text
                    ]
                ],
                'max_tokens' => 10,
                'temperature' => 0.1
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        $classification = strtolower(trim($result['choices'][0]['message']['content']));
        
        return in_array($classification, ['command', 'description']) ? $classification : 'description';
    }

    protected function processWithContext($text, $type, $context)
    {
        $prompt = $type === 'command' 
            ? $this->getCommandProcessingPrompt($context)
            : $this->getDescriptionProcessingPrompt($context);

        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $prompt
                    ],
                    [
                        'role' => 'user',
                        'content' => $text
                    ]
                ]
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function handleInterruption($currentSessionId, $newAudioData)
    {
        // Handle voice interruptions
        session()->forget("voice_session:{$currentSessionId}");
        return $this->processVoiceInput($newAudioData);
    }

    public function textToSpeech($text, $language = 'en')
    {
        // Generate speech from text for voice responses
        try {
            $response = $this->client->post('https://api.openai.com/v1/audio/speech', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                ],
                'json' => [
                    'model' => 'tts-1',
                    'input' => $text,
                    'voice' => $this->getVoiceForLanguage($language),
                    'response_format' => 'mp3'
                ]
            ]);

            $audioContent = $response->getBody()->getContents();
            $filename = 'voice_response_' . time() . '.mp3';
            Storage::put("public/voice/{$filename}", $audioContent);
            
            return url("storage/voice/{$filename}");

        } catch (\Exception $e) {
            Log::error('TTS error: ' . $e->getMessage());
            return null;
        }
    }

    protected function getVoiceForLanguage($language)
    {
        $voices = [
            'en' => 'alloy',
            'es' => 'echo',
            'fr' => 'fable',
            'de' => 'onyx',
            'zh' => 'nova'
        ];
        
        return $voices[$language] ?? 'alloy';
    }
}