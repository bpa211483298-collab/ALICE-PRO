import React from 'react';

const EnvironmentStatus = ({ environment, data, onDeploy, onRollback }) => {
    const getStatusColor = (status) => {
        const statusColors = {
            'live': 'green',
            'building': 'blue',
            'failed': 'red',
            'queued': 'yellow',
            'offline': 'gray'
        };
        return statusColors[status] || 'gray';
    };

    const getStatusIcon = (status) => {
        const statusIcons = {
            'live': '✅',
            'building': '🔄',
            'failed': '❌',
            'queued': '⏳',
            'offline': '🔌'
        };
        return statusIcons[status] || '❓';
    };

    return (
        <div className="environment-card">
            <div className="card-header">
                <h3>{environment.toUpperCase()}</h3>
                <span className={`status status-${getStatusColor(data.status)}`}>
                    {getStatusIcon(data.status)} {data.status}
                </span>
            </div>

            <div className="card-body">
                <div className="environment-info">
                    <div className="info-row">
                        <label>URL:</label>
                        <a href={data.url} target="_blank" rel="noopener noreferrer">
                            {data.url}
                        </a>
                    </div>
                    
                    <div className="info-row">
                        <label>Last Deployed:</label>
                        <span>{new Date(data.last_deployed).toLocaleString()}</span>
                    </div>
                    
                    <div className="info-row">
                        <label>Version:</label>
                        <span className="version">{data.version}</span>
                    </div>
                    
                    {data.database && (
                        <div className="info-row">
                            <label>Database:</label>
                            <span className={`db-status db-${data.database.status}`}>
                                {data.database.status}
                            </span>
                        </div>
                    )}
                </div>

                <div className="environment-actions">
                    <button 
                        className="btn btn-sm btn-primary"
                        onClick={onDeploy}
                        disabled={data.status === 'building'}
                    >
                        Deploy
                    </button>
                    
                    {data.previous_versions && data.previous_versions.length > 0 && (
                        <div className="rollback-menu">
                            <button className="btn btn-sm btn-outline">
                                Rollback
                            </button>
                            <div className="rollback-options">
                                {data.previous_versions.map(version => (
                                    <div 
                                        key={version.id}
                                        className="rollback-option"
                                        onClick={() => onRollback(version.id)}
                                    >
                                        Version {version.id} - {new Date(version.timestamp).toLocaleString()}
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {data.metrics && (
                <div className="card-footer">
                    <div className="mini-metrics">
                        <div className="metric">
                            <span className="metric-label">CPU</span>
                            <span className="metric-value">{data.metrics.cpu}%</span>
                        </div>
                        <div className="metric">
                            <span className="metric-label">Memory</span>
                            <span className="metric-value">{data.metrics.memory}%</span>
                        </div>
                        <div className="metric">
                            <span className="metric-label">Uptime</span>
                            <span className="metric-value">{data.metrics.uptime}</span>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default EnvironmentStatus;