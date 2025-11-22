import React, { useState } from 'react';

const VoiceCommandHelp = () => {
    const [isOpen, setIsOpen] = useState(false);
    
    const commands = {
        'Navigation': [
            '"Go to projects"',
            '"Open dashboard"',
            '"Show my settings"',
            '"Take me home"'
        ],
        'Project Management': [
            '"Create a new project"',
            '"Deploy my application"',
            '"Test current project"',
            '"Show project status"'
        ],
        'Development': [
            '"Add user authentication"',
            '"Create contact form"',
            '"Add database integration"',
            '"Make it responsive"'
        ],
        'System': [
            '"Help"',
            '"What can I say?"',
            '"Stop listening"',
            '"Change language to Spanish"'
        ]
    };

    return (
        <div className="voice-command-help">
            <button 
                className="help-button"
                onClick={() => setIsOpen(!isOpen)}
            >
                ❓ Voice Help
            </button>

            {isOpen && (
                <div className="help-modal">
                    <div className="modal-content">
                        <h3>Voice Commands</h3>
                        <div className="commands-grid">
                            {Object.entries(commands).map(([category, cmds]) => (
                                <div key={category} className="command-category">
                                    <h4>{category}</h4>
                                    <ul>
                                        {cmds.map((cmd, index) => (
                                            <li key={index}>{cmd}</li>
                                        ))}
                                    </ul>
                                </div>
                            ))}
                        </div>
                        <button 
                            className="close-button"
                            onClick={() => setIsOpen(false)}
                        >
                            Close
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
};

export default VoiceCommandHelp;