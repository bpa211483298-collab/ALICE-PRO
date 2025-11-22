import React, { useState, useEffect } from 'react';
import { useSocket } from '../hooks/useSocket';
import ProjectCard from './ProjectCard';
import DeploymentStatus from './DeploymentStatus';
import VoiceCommand from './VoiceCommand';

const RealTimeDashboard = () => {
    const [projects, setProjects] = useState([]);
    const [stats, setStats] = useState({});
    const [realTimeUpdates, setRealTimeUpdates] = useState([]);
    const socket = useSocket();

    useEffect(() => {
        // Load initial data
        loadDashboardData();

        // Setup socket listeners
        if (socket) {
            socket.on('project_update', (data) => {
                setRealTimeUpdates(prev => [...prev, data]);
                updateProjectStatus(data);
            });

            socket.on('deployment_status', (data) => {
                updateDeploymentStatus(data);
            });
        }

        return () => {
            if (socket) {
                socket.off('project_update');
                socket.off('deployment_status');
            }
        };
    }, [socket]);

    const loadDashboardData = async () => {
        try {
            const response = await fetch('/api/projects/dashboard');
            const data = await response.json();
            setProjects(data.recent_projects);
            setStats(data.stats);
        } catch (error) {
            console.error('Failed to load dashboard:', error);
        }
    };

    const updateProjectStatus = (update) => {
        setProjects(prev => prev.map(project => 
            project._id === update.project_id 
                ? { ...project, status: update.status, build_logs: update.logs }
                : project
        ));
    };

    return (
        <div className="min-h-screen bg-gray-100">
            <div className="container mx-auto px-4 py-8">
                {/* Header */}
                <div className="flex justify-between items-center mb-8">
                    <h1 className="text-3xl font-bold text-gray-800">ALICE Pro Dashboard</h1>
                    <VoiceCommand />
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <StatCard title="Total Projects" value={stats.total_projects} />
                    <StatCard title="Completed" value={stats.completed_projects} />
                    <StatCard title="Deployed" value={stats.deployed_projects} />
                    <StatCard title="Active" value={stats.active_deployments} />
                </div>

                {/* Real-time Updates */}
                <div className="mb-8">
                    <h2 className="text-xl font-semibold mb-4">Real-time Updates</h2>
                    <div className="space-y-2">
                        {realTimeUpdates.slice(-5).map((update, index) => (
                            <UpdateNotification key={index} update={update} />
                        ))}
                    </div>
                </div>

                {/* Projects Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    {projects.map(project => (
                        <ProjectCard key={project._id} project={project} />
                    ))}
                </div>
            </div>
        </div>
    );
};

export default RealTimeDashboard;