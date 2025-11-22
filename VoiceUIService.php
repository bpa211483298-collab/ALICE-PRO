<?php

namespace App\Services;

class VoiceUIService
{
    /**
     * Generate voice interaction components for the frontend
     */
    public static function generateVoiceUI(string $framework = 'react'): array
    {
        $components = [
            'VoiceInterface' => self::getVoiceInterfaceComponent($framework),
            'VoiceCommandProcessor' => self::getCommandProcessor(),
            'voice-commands.json' => self::getDefaultVoiceCommands()
        ];

        return $components;
    }

    protected static function getVoiceInterfaceComponent(string $framework): string
    {
        if ($framework === 'vue') {
            return self::getVueVoiceInterface();
        }
        
        // Default to React
        return self::getReactVoiceInterface();
    }

    protected static function getReactVoiceInterface(): string
    {
        return <<<'JSX'
import React, { useState, useEffect, useRef } from 'react';
import { FaMicrophone, FaStop } from 'react-icons/fa';

export default function VoiceInterface({ onCommand, onTranscription }) {
  const [isListening, setIsListening] = useState(false);
  const recognition = useRef(null);
  const [browserSupport, setBrowserSupport] = useState(true);

  useEffect(() => {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    
    if (!SpeechRecognition) {
      setBrowserSupport(false);
      return;
    }

    recognition.current = new SpeechRecognition();
    recognition.current.continuous = true;
    recognition.current.interimResults = true;

    recognition.current.onresult = (event) => {
      const transcript = Array.from(event.results)
        .map(result => result[0])
        .map(result => result.transcript)
        .join('');
      
      if (onTranscription) {
        onTranscription(transcript);
      }

      if (event.results[0].isFinal && onCommand) {
        onCommand(transcript);
      }
    };

    recognition.current.onerror = (event) => {
      console.error('Speech recognition error', event.error);
      setIsListening(false);
    };

    return () => {
      if (recognition.current) {
        recognition.current.stop();
      }
    };
  }, [onCommand, onTranscription]);

  const toggleListening = () => {
    if (isListening) {
      recognition.current.stop();
    } else {
      try {
        recognition.current.start();
      } catch (err) {
        console.error('Error starting voice recognition:', err);
      }
    }
    setIsListening(!isListening);
  };

  if (!browserSupport) {
    return (
      <div className="text-red-500 p-4 bg-red-100 rounded-lg">
        Your browser does not support the Web Speech API. Please use Chrome, Edge, or Safari.
      </div>
    );
  }

  return (
    <div className="fixed bottom-4 right-4 z-50">
      <button
        onClick={toggleListening}
        className={`p-4 rounded-full shadow-lg transition-all ${
          isListening ? 'bg-red-500 animate-pulse' : 'bg-blue-500 hover:bg-blue-600'
        }`}
        aria-label={isListening ? 'Stop listening' : 'Start voice command'}
      >
        {isListening ? (
          <FaStop className="text-white text-xl" />
        ) : (
          <FaMicrophone className="text-white text-xl" />
        )}
      </button>
      {isListening && (
        <div className="absolute -top-2 -right-2 w-4 h-4 bg-red-500 rounded-full animate-ping"></div>
      )}
    </div>
  );
}
JSX;
    }

    protected static function getVueVoiceInterface(): string
    {
        return <<<'VUE'
<template>
  <div class="voice-interface">
    <button
      @click="toggleListening"
      :class="[
        'p-4 rounded-full shadow-lg transition-all',
        isListening ? 'bg-red-500 animate-pulse' : 'bg-blue-500 hover:bg-blue-600'
      ]"
      :aria-label="isListening ? 'Stop listening' : 'Start voice command'"
    >
      <i v-if="isListening" class="fas fa-stop text-white"></i>
      <i v-else class="fas fa-microphone text-white"></i>
    </button>
    <div v-if="isListening" class="absolute -top-2 -right-2 w-4 h-4 bg-red-500 rounded-full animate-ping"></div>
  </div>
</template>

<script>
export default {
  name: 'VoiceInterface',
  props: {
    onCommand: {
      type: Function,
      default: null
    },
    onTranscription: {
      type: Function,
      default: null
    }
  },
  data() {
    return {
      isListening: false,
      recognition: null,
      browserSupport: true
    };
  },
  mounted() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    
    if (!SpeechRecognition) {
      this.browserSupport = false;
      return;
    }

    this.recognition = new SpeechRecognition();
    this.recognition.continuous = true;
    this.recognition.interimResults = true;

    this.recognition.onresult = (event) => {
      const transcript = Array.from(event.results)
        .map(result => result[0])
        .map(result => result.transcript)
        .join('');
      
      if (this.onTranscription) {
        this.onTranscription(transcript);
      }

      if (event.results[0].isFinal && this.onCommand) {
        this.onCommand(transcript);
      }
    };

    this.recognition.onerror = (event) => {
      console.error('Speech recognition error', event.error);
      this.isListening = false;
    };
  },
  beforeDestroy() {
    if (this.recognition) {
      this.recognition.stop();
    }
  },
  methods: {
    toggleListening() {
      if (this.isListening) {
        this.recognition.stop();
      } else {
        try {
          this.recognition.start();
        } catch (err) {
          console.error('Error starting voice recognition:', err);
        }
      }
      this.isListening = !this.isListening;
    }
  }
};
</script>

<style scoped>
.voice-interface {
  position: fixed;
  bottom: 1rem;
  right: 1rem;
  z-index: 50;
}
</style>
VUE;
    }

    protected static function getCommandProcessor(): string
    {
        return <<<'JS'
class VoiceCommandProcessor {
  constructor(commands = []) {
    this.commands = commands;
    this.commandHistory = [];
    this.maxHistory = 10;
  }

  processCommand(transcript) {
    if (!transcript) return { action: null, params: {}, confidence: 0 };

    // Add to command history
    this.commandHistory.unshift({
      command: transcript,
      timestamp: new Date().toISOString()
    });

    // Keep history size in check
    if (this.commandHistory.length > this.maxHistory) {
      this.commandHistory.pop();
    }

    // Simple command matching
    const normalizedTranscript = transcript.toLowerCase().trim();
    
    // Check for exact matches first
    for (const cmd of this.commands) {
      if (normalizedTranscript === cmd.command.toLowerCase()) {
        return {
          action: cmd.action,
          params: {},
          confidence: 1.0,
          command: cmd.command
        };
      }
    }

    // Check for parameterized matches
    for (const cmd of this.commands) {
      const paramMatch = cmd.command.match(/\[(.*?)\]/);
      if (paramMatch) {
        const paramName = paramMatch[1];
        const baseCmd = cmd.command.replace(/\[.*?\]/, "(.*)");
        const regex = new RegExp(`^${baseCmd}$`, "i");
        const match = transcript.match(regex);
        
        if (match && match[1]) {
          return {
            action: cmd.action,
            params: { [paramName]: match[1].trim() },
            confidence: 0.9,
            command: cmd.command
          };
        }
      }
    }

    // No match found
    return {
      action: 'unknown',
      params: { transcript },
      confidence: 0,
      command: null
    };
  }
}

export default VoiceCommandProcessor;
JS;
    }

    protected static function getDefaultVoiceCommands(): string
    {
        return json_encode([
            [
                'command' => 'create new [item]',
                'description' => 'Create a new item',
                'action' => 'createItem'
            ],
            [
                'command' => 'navigate to [page]',
                'description' => 'Navigate to a page',
                'action' => 'navigate'
            ],
            [
                'command' => 'search for [query]',
                'description' => 'Search for content',
                'action' => 'search'
            ],
            [
                'command' => 'save changes',
                'description' => 'Save current changes',
                'action' => 'save'
            ],
            [
                'command' => 'delete [item]',
                'description' => 'Delete an item',
                'action' => 'delete'
            ]
        ], JSON_PRETTY_PRINT);
    }
}
