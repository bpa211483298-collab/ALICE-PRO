import React, { useState, useEffect } from 'react';
import EnvironmentStatus from './EnvironmentStatus';
import DeploymentHistory from './DeploymentHistory';
import InfrastructureMetrics from './InfrastructureMetrics';

const DeploymentDashboard = ({ project }) => {
    const [environments, setEnvironments] = useState({});
    const [deploymentHistory, setDeploymentHistory] = useState([]);
    const [metrics, setMetrics] = useState({});
    const [isDeploying, setIsDeploying] = useState(false);

    useEffect(() => {
        loadDeploymentData();
        setupRealTimeUpdates();
    }, [project.id]);

    const loadDeploymentData = async () => {
        try {
            const [envResponse, historyResponse, metricsResponse] = await Promise.all([
                fetch(`/api/projects/${project.id}/environments`),
                fetch(`/api/projects/${project.id}/deployments/history`),
                fetch(`/api/projects/${project.id}/metrics`)
            ]);

            setEnvironments(await envResponse.json());
            setDeploymentHistory(await historyResponse.json());
            setMetrics(await metricsResponse.json());
        } catch (error) {
            console.error('Failed to load deployment data:', error);
        }
    };

    const handleDeploy = async (environment) => {
        setIsDeploying(true);
        try {
            const response = await fetch(`/api/projects/${project.id}/deploy`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ environment })
            });

            const result = await response.json();
            
            if (result.success) {
                // Refresh data
                await loadDeploymentData();
            } else {
                alert('Deployment failed: ' + result.error);
            }
        } catch (error) {
            console.error('Deployment error:', error);
            alert('Deployment failed');
        } finally {
            setIsDeploying(false);
        }
    };

    const handleRollback = async (environment, version) => {
        if (!window.confirm(`Rollback ${environment} to version ${version}?`)) return;

        try {
            const response = await fetch(`/api/projects/${project.id}/rollback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ environment, version })
            });

            const result = await response.json();
            
            if (result.success) {
                alert(`Successfully rolled back ${environment}`);
                await loadDeploymentData();
            } else {
                alert('Rollback failed: ' + result.error);
            }
        } catch (error) {
            console.error('Rollback error:', error);
            alert('Rollback failed');
        }
    };

    return (
        <div className="deployment-dashboard">
            <div className="dashboard-header">
                <h2>Deployment Management</h2>
                <div className="header-actions">
                    <button 
                        className="btn btn-primary"
                        onClick={() => handleDeploy('production')}
                        disabled={isDeploying}
                    >
                        {isDeploying ? 'Deploying...' : 'Deploy to Production'}
                    </button>
                </div>
            </div>

            <div className="environments-grid">
                {Object.entries(environments).map(([env, data]) => (
                    <EnvironmentStatus
                        key={env}
                        environment={env}
                        data={data}
                        onDeploy={() => handleDeploy(env)}
                        onRollback={(version) => handleRollback(env, version)}
                    />
                ))}
            </div>

            <div className="dashboard-row">
                <div className="col-6">
                    <DeploymentHistory history={deploymentHistory} />
                </div>
                <div className="col-6">
                    <InfrastructureMetrics metrics={metrics} />
                </div>
            </div>

            <div className="advanced-actions">
                <h3>Advanced Deployment</h3>
                <div className="action-buttons">
                    <button className="btn btn-outline">
                        Blue-Green Switch
                    </button>
                    <button className="btn btn-outline">
                        Provision Test Environment
                    </button>
                    <button className="btn btn-outline">
                        Configure Custom Domain
                    </button>
                </div>
            </div>
        </div>
    );
};

export default DeploymentDashboard;