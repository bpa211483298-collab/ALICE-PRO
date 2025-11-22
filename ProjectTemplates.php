<?php

namespace App\Services;

class ProjectTemplates
{
    /**
     * Get a pre-built project structure for a specific type of application
     * 
     * @param string $type Type of application (e.g., 'react-spa', 'nextjs', 'vue-ssr', 'api-service')
     * @param array $options Additional configuration options
     * @return array Structured project template
     */
    public static function getTemplate(string $type, array $options = []): array
    {
        $templates = [
            // Single Page Application (React)
            'react-spa' => [
                'structure' => [
                    'public/' => [
                        'index.html' => '<!-- Base HTML template -->',
                        'favicon.ico' => '',
                        'robots.txt' => 'User-agent: *\nDisallow:',
                        'assets/' => [
                            'images/' => [],
                            'fonts/' => []
                        ]
                    ],
                    'src/' => [
                        'components/' => [
                            'common/' => [
                                'Button.jsx' => '// Reusable button component',
                                'Card.jsx' => '// Card component',
                                'Layout.jsx' => '// Main layout component'
                            ],
                            'features/' => [
                                'auth/' => [
                                    'Login.jsx',
                                    'Register.jsx',
                                    'ForgotPassword.jsx'
                                ]
                            ]
                        ],
                        'pages/' => [
                            'Home.jsx',
                            'About.jsx',
                            'Contact.jsx',
                            'NotFound.jsx'
                        ],
                        'services/' => [
                            'api.js' => '// API service configuration',
                            'auth.js' => '// Authentication service'
                        ],
                        'utils/' => [
                            'helpers.js',
                            'validators.js'
                        ],
                        'styles/' => [
                            'main.scss',
                            'variables.scss',
                            'mixins.scss'
                        ],
                        'App.jsx' => '// Main application component',
                        'index.js' => '// Application entry point',
                        'routes.js' => '// Application routes'
                    ],
                    '.env' => 'REACT_APP_API_URL=https://api.example.com',
                    'package.json' => '{\n  "name": "react-spa-template",
  "version": "1.0.0",
  "private": true,
  "dependencies": {
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "react-router-dom": "^6.14.0",
    "axios": "^1.4.0",
    "sass": "^1.64.1"
  },
  "scripts": {
    "start": "react-scripts start",
    "build": "react-scripts build",
    "test": "react-scripts test",
    "eject": "react-scripts eject"
  }
}',
                    'README.md' => '# React SPA Template\n\nA modern React single page application template with common configurations and folder structure.'
                ],
                'features' => [
                    'React 18',
                    'React Router',
                    'Sass support',
                    'Environment variables',
                    'API service layer',
                    'Component-based architecture'
                ]
            ],

            // Next.js Application
            'nextjs' => [
                'structure' => [
                    'public/' => [
                        'images/' => [],
                        'favicon.ico' => ''
                    ],
                    'src/' => [
                        'app/' => [
                            'layout.js' => '// Root layout',
                            'page.js' => '// Home page',
                            'globals.css' => '// Global styles',
                            'api/' => [
                                'auth/' => [
                                    '[...nextauth]/route.js' => '// Authentication API routes'
                                ]
                            ]
                        ],
                        'components/' => [
                            'ui/' => [
                                'button.jsx',
                                'card.jsx',
                                'input.jsx'
                            ],
                            'layout/' => [
                                'header.jsx',
                                'footer.jsx',
                                'sidebar.jsx'
                            ]
                        ],
                        'lib/' => [
                            'utils.js',
                            'api.js'
                        ],
                        'styles/' => [
                            'globals.css',
                            'theme.js'
                        ]
                    ],
                    'next.config.js' => '// Next.js configuration\nmodule.exports = {\n  reactStrictMode: true,\n  swcMinify: true,\n  images: {\n    domains: ["example.com"],\n  },\n};',
                    'package.json' => '{\n  "name": "nextjs-template",\n  "version": "0.1.0",\n  "private": true,\n  "scripts": {\n    "dev": "next dev",\n    "build": "next build",\n    "start": "next start",\n    "lint": "next lint"\n  },\n  "dependencies": {\n    "next": "13.4.0",\n    "react": "18.2.0",\n    "react-dom": "18.2.0"\n  }\n}'
                ],
                'features' => [
                    'Next.js 13+',
                    'App Router',
                    'Server Components',
                    'API Routes',
                    'Built-in CSS and Sass support',
                    'Image Optimization',
                    'File-system based routing'
                ]
            ],

            // REST API Service (Node.js + Express)
            'api-service' => [
                'structure' => [
                    'src/' => [
                        'controllers/' => [
                            'userController.js',
                            'authController.js',
                            'apiController.js'
                        ],
                        'models/' => [
                            'User.js',
                            'index.js' // DB connection and model exports
                        ],
                        'routes/' => [
                            'api.js',
                            'auth.js'
                        ],
                        'middleware/' => [
                            'auth.js',
                            'errorHandler.js',
                            'validation.js'
                        ],
                        'config/' => [
                            'database.js',
                            'passport.js',
                            'cors.js'
                        ],
                        'utils/' => [
                            'logger.js',
                            'validators.js',
                            'helpers.js'
                        ],
                        'services/' => [
                            'emailService.js',
                            'storageService.js'
                        ]
                    ],
                    'tests/' => [
                        'unit/' => [],
                        'integration/' => [],
                        'e2e/' => []
                    ],
                    '.env' => 'NODE_ENV=development\nPORT=3000\nMONGODB_URI=mongodb://localhost:27017/mydb\nJWT_SECRET=your_jwt_secret\nAPI_VERSION=v1',
                    'app.js' => '// Main application entry point',
                    'server.js' => '// Server configuration and startup',
                    'package.json' => '{\n  "name": "node-api-template",\n  "version": "1.0.0",\n  "description": "RESTful API service template",\n  "main": "server.js",\n  "scripts": {\n    "start": "node server.js",\n    "dev": "nodemon server.js",\n    "test": "jest",\n    "lint": "eslint .",\n    "migrate": "node scripts/migrate.js"\n  },\n  "dependencies": {\n    "express": "^4.18.2",\n    "mongoose": "^7.3.0",\n    "cors": "^2.8.5",\n    "dotenv": "^16.3.1",\n    "jsonwebtoken": "^9.0.0",\n    "express-validator": "^7.0.1",\n    "winston": "^3.9.0",\n    "helmet": "^7.0.0"\n  },\n  "devDependencies": {\n    "jest": "^29.5.0",\n    "supertest": "^6.3.3",\n    "nodemon": "^3.0.1",\n    "eslint": "^8.44.0",\n    "eslint-config-prettier": "^8.8.0"\n  }\n}',
                    'README.md' => '# Node.js API Template\n\nA production-ready Node.js API template with Express, MongoDB, and JWT authentication.'
                ],
                'features' => [
                    'RESTful API design',
                    'MongoDB with Mongoose',
                    'JWT Authentication',
                    'Input validation',
                    'Error handling',
                    'Logging',
                    'Environment configuration',
                    'Testing setup',
                    'Security best practices'
                ]
            ],

            // Vue.js Single Page Application
            'vue-spa' => [
                'structure' => [
                    'public/' => [
                        'index.html' => '<!DOCTYPE html>\n<html>\n  <head>\n    <meta charset="utf-8">\n    <meta name="viewport" content="width=device-width,initial-scale=1.0">\n    <title>Vue SPA</title>\n  </head>\n  <body>\n    <div id="app"></div>\n  </body>\n</html>',
                        'favicon.ico' => ''
                    ],
                    'src/' => [
                        'assets/' => [
                            'styles/' => [
                                'main.scss',
                                'variables.scss',
                                'mixins.scss'
                            ],
                            'images/' => [],
                            'fonts/' => []
                        ],
                        'components/' => [
                            'common/' => [
                                'BaseButton.vue',
                                'BaseInput.vue',
                                'BaseCard.vue'
                            ],
                            'layout/' => [
                                'AppHeader.vue',
                                'AppFooter.vue',
                                'AppSidebar.vue'
                            ]
                        ],
                        'composables/' => [
                            'useApi.js',
                            'useForm.js',
                            'useAuth.js'
                        ],
                        'router/' => [
                            'index.js' // Vue Router configuration
                        ],
                        'stores/' => [
                            'index.js', // Pinia stores
                            'userStore.js',
                            'uiStore.js'
                        ],
                        'views/' => [
                            'HomeView.vue',
                            'AboutView.vue',
                            'LoginView.vue',
                            'NotFound.vue'
                        ],
                        'App.vue' => '<!-- Root component -->',
                        'main.js' => '// Application entry point',
                        'api.js' => '// API service configuration'
                    ],
                    '.env' => 'VUE_APP_API_URL=https://api.example.com\nVUE_APP_ENV=development',
                    'vite.config.js' => 'import { defineConfig } from "vite";\nimport vue from "@vitejs/plugin-vue";\n\nexport default defineConfig({\n  plugins: [vue()],\n  server: {\n    port: 3000,\n  },\n  resolve: {\n    alias: {\n      "@": "/src",\n    },\n  },\n});',
                    'package.json' => '{\n  "name": "vue-spa-template",\n  "version": "0.1.0",\n  "private": true,\n  "scripts": {\n    "dev": "vite",\n    "build": "vite build",\n    "preview": "vite preview",\n    "lint": "eslint . --ext .vue,.js,.jsx,.cjs,.mjs --fix --ignore-path .gitignore"\n  },\n  "dependencies": {\n    "vue": "^3.3.0",\n    "vue-router": "^4.2.0",\n    "pinia": "^2.1.0",\n    "axios": "^1.4.0",\n    "sass": "^1.64.1"\n  },\n  "devDependencies": {\n    "@vitejs/plugin-vue": "^4.2.0",\n    "vite": "^4.3.0",\n    "eslint": "^8.44.0",\n    "eslint-plugin-vue": "^9.14.0"\n  }\n}'
                ],
                'features' => [
                    'Vue 3 Composition API',
                    'Vue Router',
                    'Pinia for state management',
                    'Vite for fast development',
                    'Sass support',
                    'Environment variables',
                    'API service layer',
                    'Component-based architecture',
                    'TypeScript support (optional)'
                ]
            ]
        ];

        // Return the requested template or throw an exception if not found
        if (!isset($templates[$type])) {
            throw new \InvalidArgumentException("Template '{$type}' not found");
        }

        return $templates[$type];
    }

    /**
     * Get a list of all available template types
     * 
     * @return array List of template types with descriptions
     */
    public static function getAvailableTemplates(): array
    {
        return [
            'react-spa' => 'React Single Page Application',
            'nextjs' => 'Next.js Application with App Router',
            'vue-spa' => 'Vue 3 Single Page Application',
            'api-service' => 'Node.js REST API Service',
            // More templates can be added here
        ];
    }

    /**
     * Generate a new project from a template
     * 
     * @param string $templateType Type of template to use
     * @param string $targetDir Directory to generate the project in
     * @param array $options Additional options for project generation
     * @return array Result of the generation process
     */
    public static function generateProject(string $templateType, string $targetDir, array $options = []): array
    {
        $template = self::getTemplate($templateType);
        
        // Create target directory if it doesn't exist
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $createdFiles = [];
        
        // Process each file/directory in the template
        foreach ($template['structure'] as $path => $content) {
            $fullPath = rtrim($targetDir, '/') . '/' . trim($path, '/');
            
            if (is_array($content)) {
                // It's a directory
                if (!is_dir($fullPath)) {
                    mkdir($fullPath, 0755, true);
                    $createdFiles[] = $fullPath . '/';
                }
                
                // Process directory contents recursively
                if (!empty($content)) {
                    $nestedFiles = self::generateDirectoryContents($fullPath, $content);
                    $createdFiles = array_merge($createdFiles, $nestedFiles);
                }
            } else {
                // It's a file
                file_put_contents($fullPath, $content);
                $createdFiles[] = $fullPath;
            }
        }

        return [
            'success' => true,
            'template' => $templateType,
            'target_dir' => realpath($targetDir),
            'files_created' => $createdFiles,
            'features' => $template['features'] ?? []
        ];
    }

    /**
     * Recursively generate directory contents
     */
    private static function generateDirectoryContents(string $basePath, array $contents, array &$createdFiles = []): array
    {
        foreach ($contents as $name => $content) {
            $fullPath = rtrim($basePath, '/') . '/' . $name;
            
            if (is_array($content)) {
                // It's a directory
                if (!is_dir($fullPath)) {
                    mkdir($fullPath, 0755, true);
                    $createdFiles[] = $fullPath . '/';
                }
                
                // Process directory contents recursively
                if (!empty($content)) {
                    self::generateDirectoryContents($fullPath, $content, $createdFiles);
                }
            } else {
                // It's a file
                file_put_contents($fullPath, $content);
                $createdFiles[] = $fullPath;
            }
        }
        
        return $createdFiles;
    }
}
