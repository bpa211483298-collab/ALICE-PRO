import { useState, useEffect, useRef } from 'react';

export default function VoiceInput({ id, onResult, disabled = false, className = '' }) {
    const [isListening, setIsListening] = useState(false);
    const [error, setError] = useState('');
    const recognitionRef = useRef(null);

    useEffect(() => {
        // Initialize speech recognition
        if (typeof window !== 'undefined') {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            
            if (!SpeechRecognition) {
                setError('Speech recognition is not supported in your browser');
                return;
            }

            recognitionRef.current = new SpeechRecognition();
            recognitionRef.current.continuous = false;
            recognitionRef.current.interimResults = false;
            recognitionRef.current.lang = 'en-US';

            recognitionRef.current.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                if (onResult) {
                    onResult(transcript);
                }
                setIsListening(false);
            };

            recognitionRef.current.onerror = (event) => {
                console.error('Speech recognition error:', event.error);
                setError(`Error: ${event.error}`);
                setIsListening(false);
            };

            recognitionRef.current.onend = () => {
                if (isListening) {
                    recognitionRef.current.start();
                }
            };
        }

        return () => {
            if (recognitionRef.current) {
                recognitionRef.current.stop();
            }
        };
    }, [isListening, onResult]);

    const toggleListening = () => {
        if (isListening) {
            recognitionRef.current?.stop();
            setIsListening(false);
        } else {
            setError('');
            try {
                recognitionRef.current?.start();
                setIsListening(true);
            } catch (err) {
                console.error('Error starting speech recognition:', err);
                setError('Error accessing microphone. Please check permissions.');
                setIsListening(false);
            }
        }
    };

    return (
        <div className={`relative ${className}`}>
            <button
                id={id}
                type="button"
                onClick={toggleListening}
                disabled={disabled}
                aria-label={isListening ? 'Stop listening' : 'Start voice input'}
                className={`p-2 rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors ${
                    isListening 
                        ? 'bg-red-500 hover:bg-red-600 text-white focus:ring-red-500'
                        : 'bg-gray-200 hover:bg-gray-300 text-gray-700 focus:ring-gray-400'
                } ${disabled ? 'opacity-50 cursor-not-allowed' : ''}`}
                title={isListening ? 'Stop listening' : 'Start voice input'}
            >
                <svg 
                    xmlns="http://www.w3.org/2000/svg" 
                    className="h-6 w-6" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                >
                    <path 
                        strokeLinecap="round" 
                        strokeLinejoin="round" 
                        strokeWidth={2} 
                        d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" 
                    />
                </svg>
            </button>
            {isListening && (
                <div className="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-black text-white text-sm px-2 py-1 rounded">
                    Listening...
                </div>
            )}
            {error && (
                <div className="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-red-500 text-white text-sm px-2 py-1 rounded whitespace-nowrap">
                    {error}
                </div>
            )}
        </div>
    );
}
