import React from 'react';
import { Routes, Route } from 'react-router-dom';
import Layout from './Layout';
import Dashboard from '../pages/Dashboard';
import Builder from '../pages/Builder';
import Projects from '../pages/Projects';
import ProjectView from '../pages/ProjectView';
import Deployments from '../pages/Deployments';

function App() {
    return (
        <Layout>
            <Routes>
                <Route path="/" element={<Dashboard />} />
                <Route path="/builder" element={<Builder />} />
                <Route path="/projects" element={<Projects />} />
                <Route path="/project/:id" element={<ProjectView />} />
                <Route path="/deployments" element={<Deployments />} />
            </Routes>
        </Layout>
    );
}

export default App;