import React, { useState, useRef, useEffect } from 'react';
import VoiceWaveform from './VoiceWaveform';
import VoiceCommandHelp from './VoiceCommandHelp';

const VoiceInterface = ({ onVoiceResult, language = 'en' }) => {
    const [isListening, setIsListening] = useState(false);
    const [transcript, setTranscript] = useState('');
    const [audioLevel, setAudioLevel] = useState(0);
    const [isProcessing, setIsProcessing] = useState(false);
    const recognitionRef = useRef(null);
    const audioContextRef = useRef(null);
    const analyserRef = useRef(null);

    useEffect(() => {
        initializeVoiceRecognition();
        return () => {
            if (recognitionRef.current) {
                recognitionRef.current.stop();
            }
        };
    }, [language]);

    const initializeVoiceRecognition = () => {
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognitionRef.current = new SpeechRecognition();
            recognitionRef.current.continuous = true;
            recognitionRef.current.interimResults = true;
            recognitionRef.current.lang = language;

            recognitionRef.current.onresult = (event) => {
                let interimTranscript = '';
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    if (event.results[i].isFinal) {
                        const finalTranscript = event.results[i][0].transcript;
                        setTranscript(finalTranscript);
                        handleFinalTranscript(finalTranscript);
                    } else {
                        interimTranscript += event.results[i][0].transcript;
                    }
                }
                setTranscript(interimTranscript);
            };

            recognitionRef.current.onerror = (event) => {
                console.error('Speech recognition error', event.error);
                setIsListening(false);
            };
        } else {
            console.warn('Speech recognition not supported, using fallback');
        }

        setupAudioVisualization();
    };

    const setupAudioVisualization = () => {
        if (!audioContextRef.current) {
            audioContextRef.current = new (window.AudioContext || window.webkitAudioContext)();
            analyserRef.current = audioContextRef.current.createAnalyser();
            analyserRef.current.fftSize = 256;
        }
    };

    const startListening = async () => {
        if (isListening) return;
        
        try {
            if (recognitionRef.current) {
                recognitionRef.current.start();
                setIsListening(true);
                startAudioVisualization();
            } else {
                // Fallback to server-based recognition
                await startFallbackListening();
            }
        } catch (error) {
            console.error('Error starting voice recognition:', error);
            await startFallbackListening();
        }
    };

    const stopListening = () => {
        if (recognitionRef.current) {
            recognitionRef.current.stop();
        }
        setIsListening(false);
        stopAudioVisualization();
        
        if (transcript) {
            handleFinalTranscript(transcript);
        }
    };

    const handleFinalTranscript = async (finalTranscript) => {
        setIsProcessing(true);
        try {
            const response = await fetch('/api/voice/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    text: finalTranscript,
                    language: language
                })
            });
            
            const result = await response.json();
            onVoiceResult(result);
            
        } catch (error) {
            console.error('Error processing voice input:', error);
        } finally {
            setIsProcessing(false);
            setTranscript('');
        }
    };

    const startAudioVisualization = () => {
        // Setup microphone and audio visualization
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(stream => {
                const source = audioContextRef.current.createMediaStreamSource(stream);
                source.connect(analyserRef.current);
                
                const bufferLength = analyserRef.current.frequencyBinCount;
                const dataArray = new Uint8Array(bufferLength);
                
                const updateVisualization = () => {
                    if (!isListening) return;
                    
                    analyserRef.current.getByteFrequencyData(dataArray);
                    const average = dataArray.reduce((a, b) => a + b) / bufferLength;
                    setAudioLevel(average);
                    
                    requestAnimationFrame(updateVisualization);
                };
                
                updateVisualization();
            })
            .catch(error => {
                console.error('Error accessing microphone:', error);
            });
    };

    return (
        <div className="voice-interface">
            <div className="voice-controls">
                <button 
                    className={`voice-button ${isListening ? 'listening' : ''}`}
                    onMouseDown={startListening}
                    onMouseUp={stopListening}
                    onTouchStart={startListening}
                    onTouchEnd={stopListening}
                    disabled={isProcessing}
                >
                    {isProcessing ? 'Processing...' : (isListening ? 'Listening...' : 'Hold to Talk')}
                </button>
                
                <VoiceCommandHelp />
                <VoiceSettings />
            </div>

            <VoiceWaveform 
                audioLevel={audioLevel} 
                isListening={isListening}
            />

            {transcript && (
                <div className="live-transcript">
                    <h4>Live Transcript:</h4>
                    <p>{transcript}</p>
                </div>
            )}

            {isProcessing && (
                <div className="processing-indicator">
                    <div className="spinner"></div>
                    <span>Processing your command...</span>
                </div>
            )}
        </div>
    );
};

export default VoiceInterface;