import React, { useEffect, useRef } from 'react';

const VoiceWaveform = ({ audioLevel, isListening }) => {
    const canvasRef = useRef(null);
    const animationRef = useRef(null);

    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const width = canvas.width;
        const height = canvas.height;

        const drawWaveform = () => {
            ctx.clearRect(0, 0, width, height);
            
            if (!isListening) return;

            ctx.fillStyle = '#3B82F6';
            const barWidth = 4;
            const spacing = 2;
            const barCount = 20;
            
            for (let i = 0; i < barCount; i++) {
                // Create interesting wave pattern based on audio level
                const barHeight = (audioLevel / 255) * height * 
                                Math.sin(Date.now() / 200 + i * 0.3) * 
                                (0.5 + Math.random() * 0.5);
                
                ctx.fillRect(
                    i * (barWidth + spacing),
                    (height - barHeight) / 2,
                    barWidth,
                    barHeight
                );
            }

            animationRef.current = requestAnimationFrame(drawWaveform);
        };

        drawWaveform();

        return () => {
            if (animationRef.current) {
                cancelAnimationFrame(animationRef.current);
            }
        };
    }, [audioLevel, isListening]);

    return (
        <div className="voice-waveform">
            <canvas 
                ref={canvasRef} 
                width={200} 
                height={80}
                className={isListening ? 'active' : ''}
            />
        </div>
    );
};

export default VoiceWaveform;