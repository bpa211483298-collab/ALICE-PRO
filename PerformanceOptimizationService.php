<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use ImageOptimizer;

class PerformanceOptimizationService
{
    public function optimizeApplication($generatedCode, $projectType)
    {
        try {
            $optimizedCode = $generatedCode;

            // Apply all performance optimizations
            $optimizedCode = $this->applyCodeSplitting($optimizedCode, $projectType);
            $optimizedCode = $this->applyImageOptimization($optimizedCode);
            $optimizedCode = $this->applyCachingStrategies($optimizedCode, $projectType);
            $optimizedCode = $this->applyDatabaseOptimizations($optimizedCode);
            $optimizedCode = $this->applyRateLimiting($optimizedCode);
            $optimizedCode = $this->applySecurityHeaders($optimizedCode);

            return $optimizedCode;

        } catch (\Exception $e) {
            Log::error('Performance optimization error: ' . $e->getMessage());
            return $generatedCode;
        }
    }

    protected function applyCodeSplitting($code, $projectType)
    {
        if ($projectType === 'react' || $projectType === 'vue') {
            // Add React.lazy or Vue async components for code splitting
            foreach ($code as $file => $content) {
                if (strpos($file, '.jsx') !== false || strpos($file, '.vue') !== false) {
                    $content = preg_replace(
                        '/import\s+(\w+)\s+from\s+[\'"]([^\'"]+)[\'"];/',
                        'const $1 = React.lazy(() => import(\'$2\'));',
                        $content
                    );
                    $code[$file] = $content;
                }
            }

            // Add Suspense wrapper in main app component
            if (isset($code['App.jsx'])) {
                $code['App.jsx'] = str_replace(
                    '<Router>',
                    '<Suspense fallback={<div>Loading...</div>}><Router>',
                    $code['App.jsx']
                );
                $code['App.jsx'] = str_replace(
                    '</Router>',
                    '</Router></Suspense>',
                    $code['App.jsx']
                );
            }
        }

        return $code;
    }

    protected function applyImageOptimization($code)
    {
        // Add image optimization components and utilities
        $code['components/OptimizedImage.jsx'] = $this->getOptimizedImageComponent();
        $code['utils/imageOptimizer.js'] = $this->getImageOptimizerUtility();

        // Replace regular img tags with OptimizedImage components
        foreach ($code as $file => $content) {
            if (strpos($file, '.jsx') !== false || strpos($file, '.vue') !== false) {
                $content = preg_replace(
                    '/<img\s+src="([^"]+)"\s+alt="([^"]*)"\s*\/?>/',
                    '<OptimizedImage src="$1" alt="$2" />',
                    $content
                );
                $code[$file] = $content;
            }
        }

        return $code;
    }

    protected function applyCachingStrategies($code, $projectType)
    {
        // Add service worker for PWA caching
        if ($projectType === 'react' || $projectType === 'vue') {
            $code['public/sw.js'] = $this->getServiceWorkerCode();
            
            // Register service worker
            if (isset($code['index.js']) || isset($code['main.js'])) {
                $mainFile = isset($code['index.js']) ? 'index.js' : 'main.js';
                $code[$mainFile] .= $this->getServiceWorkerRegistration();
            }
        }

        // Add server-side caching headers
        if (isset($code['server.js'])) {
            $code['server.js'] = $this->addCachingHeaders($code['server.js']);
        }

        return $code;
    }

    protected function applyDatabaseOptimizations($code)
    {
        // Add database indexing and query optimization
        if (isset($code['models/'])) {
            foreach ($code['models/'] as $modelFile => $modelCode) {
                $code['models/' . $modelFile] = $this->addDatabaseIndexes($modelCode);
            }
        }

        // Add query optimization middleware
        if (isset($code['server.js'])) {
            $code['server.js'] = $this->addQueryOptimization($code['server.js']);
        }

        return $code;
    }

    protected function applyRateLimiting($code)
    {
        // Add API rate limiting
        if (isset($code['server.js'])) {
            $code['server.js'] = $this->addRateLimiting($code['server.js']);
        }

        return $code;
    }

    protected function applySecurityHeaders($code)
    {
        // Add security headers middleware
        if (isset($code['server.js'])) {
            $code['server.js'] = $this->addSecurityHeaders($code['server.js']);
        }

        return $code;
    }

    // Helper methods for various optimization code snippets
    protected function getOptimizedImageComponent()
    {
        return `
        import React, { useState } from 'react';
        import { optimizeImage } from '../utils/imageOptimizer';

        const OptimizedImage = ({ src, alt, width, height, ...props }) => {
            const [optimizedSrc, setOptimizedSrc] = useState(null);
            const [loading, setLoading] = useState(true);
            const [error, setError] = useState(false);

            useEffect(() => {
                const loadImage = async () => {
                    try {
                        const optimized = await optimizeImage(src, width, height);
                        setOptimizedSrc(optimized);
                    } catch (err) {
                        setError(true);
                        console.error('Image optimization failed:', err);
                    } finally {
                        setLoading(false);
                    }
                };

                loadImage();
            }, [src, width, height]);

            if (error) {
                return <img src={src} alt={alt} {...props} />;
            }

            return (
                <div className="optimized-image">
                    {loading && <div className="image-placeholder">Loading...</div>}
                    <img
                        src={optimizedSrc || src}
                        alt={alt}
                        style={{ opacity: loading ? 0 : 1 }}
                        onLoad={() => setLoading(false)}
                        {...props}
                    />
                </div>
            );
        };

        export default OptimizedImage;
        `;
    }

    protected function getServiceWorkerCode()
    {
        return `
        const CACHE_NAME = 'alice-pro-v1';
        const urlsToCache = [
            '/',
            '/static/js/bundle.js',
            '/static/css/main.css',
            '/manifest.json'
        ];

        self.addEventListener('install', (event) => {
            event.waitUntil(
                caches.open(CACHE_NAME)
                    .then((cache) => cache.addAll(urlsToCache))
            );
        });

        self.addEventListener('fetch', (event) => {
            event.respondWith(
                caches.match(event.request)
                    .then((response) => {
                        if (response) {
                            return response;
                        }

                        return fetch(event.request).then((response) => {
                            if (!response || response.status !== 200 || response.type !== 'basic') {
                                return response;
                            }

                            const responseToCache = response.clone();

                            caches.open(CACHE_NAME)
                                .then((cache) => {
                                    cache.put(event.request, responseToCache);
                                });

                            return response;
                        });
                    })
            );
        });
        `;
    }
}