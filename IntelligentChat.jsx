import React, { useState, useRef, useEffect } from 'react';
import { useConversation } from '../hooks/useConversation';

const IntelligentChat = ({ onRequirementsComplete }) => {
    const [messages, setMessages] = useState([]);
    const [input, setInput] = useState('');
    const [isProcessing, setIsProcessing] = useState(false);
    const { sendMessage, conversationHistory } = useConversation();
    const messagesEndRef = useRef(null);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    };

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const handleSend = async () => {
        if (!input.trim() || isProcessing) return;

        const userMessage = { role: 'user', content: input };
        setMessages(prev => [...prev, userMessage]);
        setInput('');
        setIsProcessing(true);

        try {
            const response = await sendMessage(input);
            
            if (response.complete_requirements) {
                onRequirementsComplete(response.complete_requirements);
            }

            setMessages(prev => [...prev, {
                role: 'assistant',
                content: response.clarified_requirements || response.response,
                technicalSpecs: response.technical_specifications,
                suggestions: response.ai_suggestions
            }]);

        } catch (error) {
            setMessages(prev => [...prev, {
                role: 'assistant',
                content: 'Sorry, I encountered an error. Please try again.',
                isError: true
            }]);
        } finally {
            setIsProcessing(false);
        }
    };

    return (
        <div className="intelligent-chat">
            <div className="chat-messages">
                {messages.map((msg, index) => (
                    <div key={index} className={`message ${msg.role}`}>
                        <div className="message-content">
                            {msg.content}
                        </div>
                        {msg.technicalSpecs && (
                            <div className="message-metadata">
                                <span className="badge">Technical Analysis Complete</span>
                            </div>
                        )}
                    </div>
                ))}
                {isProcessing && (
                    <div className="message assistant">
                        <div className="typing-indicator">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                )}
                <div ref={messagesEndRef} />
            </div>

            <div className="chat-input">
                <input
                    type="text"
                    value={input}
                    onChange={(e) => setInput(e.target.value)}
                    onKeyPress={(e) => e.key === 'Enter' && handleSend()}
                    placeholder="Describe your app in detail..."
                    disabled={isProcessing}
                />
                <button onClick={handleSend} disabled={isProcessing}>
                    {isProcessing ? 'Processing...' : 'Send'}
                </button>
            </div>
        </div>
    );
};

export default IntelligentChat;