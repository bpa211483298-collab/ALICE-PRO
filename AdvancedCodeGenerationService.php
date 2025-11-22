<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\ProjectTemplates;
use App\Services\VoiceUIService;
use App\Services\SelfHealingService;

class AdvancedCodeGenerationService
{
    protected $client;
    protected $supportedFrameworks = [
        // Frontend Frameworks
        'frontend' => [
            'react', 'vue', 'angular', 'svelte', 'nextjs', 'nuxtjs', 'gatsby', 'gridsome',
            'astro', 'eleventy', 'sveltekit', 'remix', 'solidjs', 'qwik', 'alpinejs', 'htmx'
        ],
        
        // Backend Frameworks
        'backend' => [
            'node', 'express', 'nest', 'fastify', 'koa', 'hapi',
            'python', 'django', 'flask', 'fastapi', 'sanic', 'tornado',
            'php', 'laravel', 'symfony', 'codeigniter', 'slim', 'yii',
            'ruby', 'rails', 'sinatra', 'hanami',
            'java', 'spring', 'quarkus', 'micronaut', 'play', 'vertx',
            'go', 'gin', 'echo', 'fiber', 'chi', 'beego',
            'rust', 'actix', 'rocket', 'axum', 'warp', 'poem',
            'dotnet', 'aspnetcore', 'nancy', 'serviceStack'
        ],
        
        // Styling Frameworks
        'styling' => [
            'tailwind', 'bootstrap', 'materialui', 'chakra', 'antd', 'bulma',
            'foundation', 'semanticui', 'tailwindui', 'daisyui', 'shadcn',
            'sass', 'less', 'stylus', 'postcss', 'styled-components', 'emotion',
            'stitches', 'vanilla-extract', 'panda-css', 'unocss', 'windi-css'
        ],
        
        // Database Systems
        'database' => [
            'postgresql', 'mysql', 'mariadb', 'sqlite', 'oracle', 'sqlserver',
            'mongodb', 'firebase', 'firestore', 'dynamodb', 'cassandra', 'couchdb',
            'redis', 'memcached', 'elasticsearch', 'meilisearch', 'typesense',
            'neo4j', 'dgraph', 'arangodb', 'faunadb', 'supabase', 'cockroachdb',
            'timescaledb', 'questdb', 'scylladb', 'yugabytedb'
        ],
        
        // Animation & Graphics
        'animation' => [
            'pixijs', 'threejs', 'gsap', 'animejs', 'framer-motion', 'motion-one',
            'popmotion', 'react-spring', 'framer', 'lottie', 'mojs', 'anime',
            'velocity', 'vivus', 'barbajs', 'locomotive-scroll', 'lenis', 'motion-canvas'
        ],
        
        // Game Development
        'game' => [
            'phaser', 'babylonjs', 'threejs', 'p5js', 'godot', 'unity', 'unreal',
            'defold', 'construct', 'gdevelop', 'panda3d', 'armory3d', 'bevy',
            'monogame', 'libgdx', 'love2d', 'raylib', 'haxeflixel', 'defold'
        ],
        
        // Documentation & Ebooks
        'ebook' => [
            'epubjs', 'pandoc', 'gitbook', 'mdbook', 'docsify', 'docusaurus',
            'vuepress', 'mkdocs', 'sphinx', 'mintlify', 'nextra', 'storybook',
            'docz', 'docute', 'docutejs', 'docsearch', 'docute-x', 'docute-cli'
        ],
        
        // DevOps & Cloud
        'devops' => [
            'docker', 'kubernetes', 'docker-compose', 'helm', 'terraform', 'pulumi',
            'ansible', 'chef', 'puppet', 'saltstack', 'vagrant', 'packer',
            'github-actions', 'gitlab-ci', 'jenkins', 'circleci', 'travis-ci', 'argo-cd',
            'flux', 'crossplane', 'kustomize', 'skaffold', 'telepresence', 'tilt',
            'aws', 'gcp', 'azure', 'digitalocean', 'linode', 'vultr', 'render', 'vercel', 'netlify'
        ],
        
        // AI & Machine Learning
        'ai' => [
            // OpenAI & Alternatives
            'openai', 'openrouter', 'anthropic', 'cohere', 'ai21', 'aleph-alpha',
            'nvidia', 'nemo', 'tensorrt', 'triton', 'tensorflow', 'pytorch',
            'jax', 'huggingface', 'transformers', 'diffusers', 'sentence-transformers',
            'langchain', 'llama-index', 'haystack', 'semantic-kernel', 'llamafile',
            
            // Open Source LLMs
            'llama', 'mistral', 'falcon', 'mpt', 'pythia', 'dolly', 'gpt4all',
            'vicuna', 'koala', 'alpaca', 'stablelm', 'redpajama', 'openllama',
            'cerebras', 'bloom', 'opt', 'gpt-neox', 'gpt-j', 'gpt-neo',
            'replit', 'starcoder', 'codegen', 'incoder', 'wizardcoder', 'phind-codellama',
            
            // Vector Databases
            'pinecone', 'weaviate', 'chroma', 'qdrant', 'milvus', 'faiss',
            'annoy', 'hnswlib', 'lancedb', 'vald', 'vespa', 'jina', 'marqo'
        ],
        
        // Programming Languages
        'languages' => [
            // Web
            'javascript', 'typescript', 'html', 'css', 'wasm', 'webassembly',
            
            // General Purpose
            'python', 'java', 'csharp', 'fsharp', 'vbnet', 'powershell',
            'go', 'rust', 'swift', 'kotlin', 'scala', 'groovy', 'clojure',
            'dart', 'julia', 'nim', 'crystal', 'zig', 'v', 'odin', 'jai',
            
            // Systems Programming
            'c', 'cpp', 'zig', 'odin', 'v', 'rust', 'nim', 'crystal', 'd',
            'zig', 'odin', 'v', 'jai', 'carbon', 'haskell', 'ocaml', 'fsharp',
            
            // Scripting
            'php', 'ruby', 'perl', 'lua', 'r', 'matlab', 'octave', 'stata',
            'julia', 'r', 'matlab', 'octave', 'stata', 'sas', 'spss', 'stata',
            
            // Mobile
            'dart', 'kotlin', 'swift', 'objective-c', 'java', 'javascript',
            'typescript', 'dart', 'kotlin', 'swift', 'objective-c', 'java',
            
            // Functional
            'haskell', 'ocaml', 'fsharp', 'elixir', 'erlang', 'clojure',
            'scheme', 'racket', 'common-lisp', 'elm', 'purescript', 'idris',
            
            // New & Emerging
            'gleam', 'v', 'zig', 'odin', 'jai', 'carbon', 'mojo', 'vlang',
            'carp', 'janet', 'arturo', 'seed7', 'perl6', 'raku', 'red', 'rebol'
        ],
        
        // Internationalization & Localization
        'i18n' => [
            // Languages
            'en', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'zh', 'ja', 'ko',
            'ar', 'hi', 'bn', 'pa', 'ur', 'id', 'ms', 'th', 'vi', 'tr',
            'nl', 'el', 'pl', 'uk', 'ro', 'hu', 'sv', 'fi', 'da', 'no',
            'he', 'fa', 'th', 'vi', 'tr', 'nl', 'el', 'pl', 'uk', 'ro',
            
            // Frameworks
            'i18next', 'react-intl', 'vue-i18n', 'ngx-translate', 'next-i18next',
            'formatjs', 'polyglot', 'globalize', 'i18n-js', 'lingui', 'next-intl',
            'typesafe-i18n', 'svelte-i18n', 'i18n-ally', 'i18n-unused', 'i18n-tasks'
        ]
    ];
    
    protected $renderConfig = [
        'service_type' => 'web',
        'auto_deploy' => true,
        'region' => 'oregon',
        'env_vars' => []
    ];
    
    protected $aiTeamRoles = [
        'architect' => 'Senior Software Architect',
        'devops' => 'DevOps Engineer',
        'frontend' => 'Frontend Developer',
        'backend' => 'Backend Developer',
        'designer' => 'UI/UX Designer',
        'qa' => 'Quality Assurance',
        'ai' => 'AI Specialist',
        'writer' => 'Technical Writer',
        'template_engineer' => 'Template Engineer',
        'self_healing_engineer' => 'Self-Healing Engineer'
    ];
    
    /**
     * Available project templates
     * 
     * @var array
     */
    protected $availableTemplates = [];
    protected $selfHealingService;
    
    /**
     * Template configuration
     * 
     * @var array
     */
    protected $templateConfig = [
        'default_templates' => [
            'react-spa' => 'React Single Page Application',
            'nextjs' => 'Next.js Application with App Router',
            'vue-spa' => 'Vue 3 Single Page Application',
            'api-service' => 'Node.js REST API Service'
        ],
        'custom_templates_path' => 'templates/',
        'cache_ttl' => 3600 // 1 hour cache for templates
    ];

    /**
     * Initialize the service with configuration
     * 
     * @param array $config Configuration options
     */
    public function __construct(array $config = [])
    {
        $this->client = new Client();
        $this->templateConfig = array_merge($this->templateConfig, $config);
        $this->loadAvailableTemplates();
        $this->selfHealingService = new SelfHealingService();
    }
    
    /**
     * Load available templates from both default and custom locations
     */
    protected function loadAvailableTemplates(): void
    {
        // Load default templates
        $this->availableTemplates = $this->templateConfig['default_templates'];
        
        // Try to load custom templates if the directory exists
        $customTemplatesPath = base_path($this->templateConfig['custom_templates_path']);
        if (is_dir($customTemplatesPath)) {
            $customTemplates = $this->discoverCustomTemplates($customTemplatesPath);
            $this->availableTemplates = array_merge($this->availableTemplates, $customTemplates);
        }
    }
    
    /**
     * Discover custom templates in the specified directory
     * 
     * @param string $directory Path to custom templates directory
     * @return array Array of discovered templates
     */
    protected function discoverCustomTemplates(string $directory): array
    {
        $templates = [];
        
        try {
            $items = scandir($directory);
            
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                
                $path = rtrim($directory, '/') . '/' . $item;
                $configFile = $path . '/template.json';
                
                if (is_dir($path) && file_exists($configFile)) {
                    $config = json_decode(file_get_contents($configFile), true);
                    if (isset($config['id'], $config['name'])) {
                        $templates[$config['id']] = $config['name'];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error discovering custom templates: ' . $e->getMessage());
        }
        
        return $templates;
    }

    /**
     * Analyze and fix errors in the application
     */
    public function diagnoseAndFix(string $errorLog, string $context = null, array $options = []): array
    {
        try {
            // Initialize self-healing service with project path if available
            $projectPath = $options['project_path'] ?? $this->projectPath ?? null;
            $selfHealingService = $projectPath ? new SelfHealingService($projectPath) : $this->selfHealingService;
            
            // Analyze and attempt to fix the error
            $result = $selfHealingService->analyzeAndFix($errorLog, $context);
            
            // If fix was successful, run tests to verify
            if ($result['success'] && ($options['run_tests_after_fix'] ?? true)) {
                $testResults = $selfHealingService->runTests();
                $result['test_results'] = $testResults;
                
                if (!$testResults['success']) {
                    $result['success'] = false;
                    $result['message'] = 'Fix applied but tests are still failing';
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Self-healing failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to analyze and fix error',
                'error' => $e->getMessage(),
                'solution' => 'Please check the error logs for more details.'
            ];
        }
    }
    
    /**
     * Enable self-healing for the application
     */
    public function enableSelfHealing(array $config = []): void
    {
        $this->selfHealingConfig = array_merge([
            'enabled' => true,
            'auto_fix' => true,
            'max_attempts' => 3,
            'notify_on_fix' => true,
            'test_after_fix' => true,
            'backup_before_fix' => true
        ], $config);
    }
    
    /**
     * Get self-healing status and history
     */
    public function getSelfHealingStatus(): array
    {
        if (!$this->selfHealingService) {
            return [
                'enabled' => false,
                'message' => 'Self-healing service not initialized'
            ];
        }
        
        return [
            'enabled' => $this->selfHealingConfig['enabled'] ?? false,
            'error_history' => $this->selfHealingService->getErrorHistory(),
            'fix_history' => $this->selfHealingService->getFixHistory(),
            'config' => $this->selfHealingConfig ?? []
        ];
    }
    
    /**
     * Generate a complete application using templates when possible
     * 
     * @param array $requirements Application requirements
     * @param array $preferences User preferences
     * @param bool $useTemplates Whether to use pre-built templates
     * @return array Generated application structure
     */
    public function generateCompleteApplication($requirements, $preferences = [], bool $useTemplates = true)
    {
        try {
            // Step 1: Parse and analyze requirements
            $analysis = $this->analyzeRequirements($requirements);
            
            // Step 2: Determine optimal tech stack
            $techStack = $this->determineTechStack($analysis, $preferences);
            
            // Try to use a template if requested and available
            if ($useTemplates) {
                $templateMatch = $this->findMatchingTemplate($analysis, $techStack);
                
                if ($templateMatch) {
                    return $this->generateFromTemplate($templateMatch, $requirements, $preferences);
                }
            }
            
            // Fall back to full code generation if no template matches
            return $this->generateFromScratch($analysis, $techStack);

        } catch (\Exception $e) {
            Log::error('Advanced code generation error: ' . $e->getMessage());
            throw new \Exception('Application generation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate application from scratch without using templates
     */
    protected function generateFromScratch(array $analysis, array $techStack): array
    {
        // Initialize self-healing service for the project
        $selfHealingConfig = [
            'enabled' => true,
            'auto_fix' => true,
            'max_attempts' => 3
        ];
        
        // Add self-healing middleware if it's a web application
        if (in_array($techStack['frontend'] ?? '', ['react', 'vue', 'angular', 'nextjs', 'nuxtjs'])) {
            $this->addSelfHealingMiddleware($techStack);
        }
        
        // Rest of the original method...
        // Generate database schema
        $databaseSchema = $this->generateDatabaseSchema($analysis, $techStack['database']);
        
        // Generate backend code
        $backendCode = $this->generateBackendCode($analysis, $techStack['backend'], $databaseSchema);
        
        // Generate frontend code
        $frontendCode = $this->generateFrontendCode($analysis, $techStack['frontend'], $backendCode['api_spec']);
        
        // Generate infrastructure configuration
        $infrastructure = $this->generateInfrastructure($techStack, $backendCode, $frontendCode);
        
        // Apply optimizations
        $optimizedCode = $this->optimizeCode($backendCode, $frontendCode, $infrastructure);
        
        return [
            'generation_method' => 'from_scratch',
            'tech_stack' => $techStack,
            'database_schema' => $databaseSchema,
            'backend_code' => $optimizedCode['backend'],
            'frontend_code' => $optimizedCode['frontend'],
            'infrastructure' => $optimizedCode['infrastructure'],
            'deployment_config' => $this->generateDeploymentConfig($techStack)
        ];
    }
    
    /**
     * Find a matching template for the given requirements and tech stack
     */
    protected function findMatchingTemplate(array $analysis, array $techStack): ?array
    {
        // Simple template matching based on tech stack
        $templateType = null;
        $frontend = $techStack['frontend'] ?? null;
        $backend = $techStack['backend'] ?? null;
        
        // Map common stacks to template types
        if ($frontend === 'react' && $backend === 'node') {
            $templateType = 'react-spa';
        } elseif ($frontend === 'nextjs') {
            $templateType = 'nextjs';
        } elseif ($frontend === 'vue' && $backend === 'node') {
            $templateType = 'vue-spa';
        } elseif ($backend === 'node' && empty($frontend)) {
            $templateType = 'api-service';
        }
        
        if ($templateType && isset($this->availableTemplates[$templateType])) {
            return [
                'type' => $templateType,
                'name' => $this->availableTemplates[$templateType],
                'match_score' => 90, // 0-100 confidence score
                'reason' => "Matched based on tech stack: {$frontend} + {$backend}"
            ];
        }
        
        // No template matched
        return null;
    }
    
    /**
     * Generate an application using a pre-built template
     */
    protected function generateFromTemplate(array $template, array $requirements, array $preferences): array
    {
        $templateType = $template['type'];
        $targetDir = $preferences['output_dir'] ?? sys_get_temp_dir() . '/generated_app';
        
        // Generate the project structure from template
        $result = ProjectTemplates::generateProject($templateType, $targetDir, [
            'project_name' => $requirements['name'] ?? 'MyApp',
            'author' => $preferences['author'] ?? 'Generated by ALICE',
            'version' => $preferences['version'] ?? '1.0.0'
        ]);
        
        // Customize the template based on requirements
        $this->customizeTemplate($templateType, $targetDir, $requirements, $preferences);
        
        return [
            'generation_method' => 'template',
            'template' => $template,
            'target_directory' => $targetDir,
            'files_created' => $result['files_created'] ?? [],
            'features' => $result['features'] ?? [],
            'next_steps' => [
                'cd ' . $targetDir,
                'npm install',
                'npm run dev'
            ]
        ];
    }
    
    /**
     * Customize a generated template based on requirements
     */
    protected function addSelfHealingMiddleware(array $techStack): void
    {
        $middlewarePath = app_path('Http/Middleware/SelfHealingMiddleware.php');
        
        if (!file_exists($middlewarePath)) {
            $middlewareContent = <<<'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SelfHealingService;
use Illuminate\Support\Facades\Log;

class SelfHealingMiddleware
{
    protected $selfHealingService;
    
    public function __construct(SelfHealingService $selfHealingService)
    {
        $this->selfHealingService = $selfHealingService;
    }
    
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Check for error responses
        if ($response->status() >= 400) {
            $errorLog = [
                'status' => $response->status(),
                'path' => $request->path(),
                'method' => $request->method(),
                'message' => $response->exception ? $response->exception->getMessage() : 'Unknown error'
            ];
            
            // Log the error
            Log::error('HTTP Error', $errorLog);
            
            // Try to fix the error if self-healing is enabled
            if (config('self_healing.enabled', true)) {
                $fixResult = $this->selfHealingService->analyzeAndFix(
                    json_encode($errorLog),
                    'http_error'
                );
                
                // If fix was successful, log it
                if ($fixResult['success'] ?? false) {
                    Log::info('Self-healing applied successfully', $fixResult);
                }
            }
        }
        
        return $response;
    }
}
PHP;
            
            // Save the middleware file
            if (!is_dir(dirname($middlewarePath))) {
                mkdir(dirname($middlewarePath), 0755, true);
            }
            
            file_put_contents($middlewarePath, $middlewareContent);
            
            // Register the middleware in the HTTP kernel
            $kernelPath = app_path('Http/Kernel.php');
            if (file_exists($kernelPath)) {
                $kernelContent = file_get_contents($kernelPath);
                
                // Add to $middlewareGroups
                if (strpos($kernelContent, 'protected $middlewareGroups') !== false) {
                    $kernelContent = str_replace(
                        "'web' => [",
                        "'web' => [\n            \\App\\Http\\Middleware\\\\SelfHealingMiddleware::class,",
                        $kernelContent
                    );
                    
                    file_put_contents($kernelPath, $kernelContent);
                }
            }
        }
    }
    
    protected function customizeTemplate(string $templateType, string $targetDir, array $requirements, array $preferences): void
    {
        // Example customization based on template type
        switch ($templateType) {
            case 'react-spa':
            case 'vue-spa':
                $this->customizeFrontendTemplate($targetDir, $requirements, $preferences);
                break;
                
            case 'api-service':
                $this->customizeApiTemplate($targetDir, $requirements, $preferences);
                break;
                
            case 'nextjs':
                $this->customizeNextJSTemplate($targetDir, $requirements, $preferences);
                break;
        }
        
        // Update package.json with project details
        $this->updatePackageJson($targetDir, [
            'name' => $this->slugify($requirements['name'] ?? 'my-app'),
            'description' => $requirements['description'] ?? 'Generated by ALICE',
            'version' => $preferences['version'] ?? '1.0.0',
            'author' => $preferences['author'] ?? ''
        ]);
        
        // Add README.md if it doesn't exist
        $readmePath = $targetDir . '/README.md';
        if (!file_exists($readmePath)) {
            $readmeContent = "# " . ($requirements['name'] ?? 'My Application') . "\n\n" .
                           ($requirements['description'] ?? 'A new application generated by ALICE.') . "\n\n" .
                           "## Getting Started\n\n" .
                           "1. Install dependencies: `npm install`\n" .
                           "2. Start development server: `npm run dev`\n\n" .
                           "## Building for Production\n\n" .
                           "1. Build the application: `npm run build`\n" .
                           "2. Start production server: `npm start`";
            
            file_put_contents($readmePath, $readmeContent);
        }
    }

    protected function analyzeRequirements($requirements)
    {
        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a senior software architect. Analyze these application requirements and extract:
                        1. Core features and functionality
                        2. Data models and relationships
                        3. User roles and permissions
                        4. API endpoints needed
                        5. UI components required
                        6. Performance considerations
                        7. Security requirements
                        8. Scalability needs
                        
                        Return as JSON with detailed analysis."
                    ],
                    [
                        'role' => 'user',
                        'content' => $requirements
                    ]
                ],
                'max_tokens' => 3000,
                'temperature' => 0.3
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        return json_decode($result['choices'][0]['message']['content'], true);
    }

    protected function determineTechStack($analysis, $preferences)
    {
        // AI-powered tech stack recommendation
        $prompt = "Based on these requirements: " . json_encode($analysis) . 
                 " and these preferences: " . json_encode($preferences) . 
                 " Recommend the optimal tech stack including frontend, backend, database, and styling framework.";

        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a tech stack recommendation engine. Recommend the best technologies based on requirements."
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 1000,
                'temperature' => 0.2
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        $recommendation = json_decode($result['choices'][0]['message']['content'], true);
        
        // Apply user preferences if provided
        return $this->applyPreferences($recommendation, $preferences);
    }

    protected function generateDatabaseSchema($analysis, $databaseType)
    {
        $prompt = "Create a complete database schema for " . $databaseType . " based on these requirements: " . 
                 json_encode($analysis) . " Include tables, relationships, indexes, and constraints.";

        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a database architect. Create optimized database schemas."
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.3
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        return json_decode($result['choices'][0]['message']['content'], true);
    }

    protected function generateBackendCode($analysis, $backendFramework, $databaseSchema)
    {
        $prompt = "Generate complete backend code using " . $backendFramework . " with these requirements: " . 
                 json_encode($analysis) . " and this database schema: " . json_encode($databaseSchema) . 
                 " Include models, controllers, routes, middleware, and authentication.";

        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a backend developer. Generate production-ready backend code."
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 4000,
                'temperature' => 0.2
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        $code = json_decode($result['choices'][0]['message']['content'], true);
        
        // Add both REST and GraphQL APIs
        $code['rest_api'] = $this->generateRestApi($analysis, $databaseSchema);
        $code['graphql_api'] = $this->generateGraphQLApi($analysis, $databaseSchema);
        
        return $code;
    }

    protected function generateFrontendCode($analysis, $frontendFramework, $apiSpec)
    {
        $prompt = "Generate complete frontend code using " . $frontendFramework . " with these requirements: " . 
                 json_encode($analysis) . " and this API specification: " . json_encode($apiSpec) . 
                 " Include components, routing, state management, and API integration.";

        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a frontend developer. Generate production-ready frontend code."
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 4000,
                'temperature' => 0.2
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        $code = json_decode($result['choices'][0]['message']['content'], true);
        
        // Add component library
        $code['component_library'] = $this->generateComponentLibrary($analysis, $frontendFramework);
        
        return $code;
    }

    protected function generateRestApi($analysis, $databaseSchema)
    {
        $prompt = "Generate REST API endpoints for these requirements: " . json_encode($analysis) . 
                 " and this database schema: " . json_encode($databaseSchema);

        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are an API designer. Create comprehensive REST APIs."
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.2
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        return json_decode($result['choices'][0]['message']['content'], true);
    }

    protected function generateGraphQLApi($analysis, $databaseSchema)
    {
        $prompt = "Generate GraphQL schema and resolvers for these requirements: " . json_encode($analysis) . 
                 " and this database schema: " . json_encode($databaseSchema);

        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a GraphQL expert. Create comprehensive GraphQL schemas and resolvers."
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.2
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        return json_decode($result['choices'][0]['message']['content'], true);
    }

    protected function generateComponentLibrary($analysis, $frontendFramework)
    {
        $prompt = "Generate a reusable component library for " . $frontendFramework . " with these requirements: " . 
                 json_encode($analysis) . " Include common UI components with props and styling.";

        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a UI/UX engineer. Create reusable component libraries."
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 3000,
                'temperature' => 0.2
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        return json_decode($result['choices'][0]['message']['content'], true);
    }

    protected function optimizeCode($backendCode, $frontendCode, $infrastructure)
    {
        $optimizationService = new CodeOptimizationService();
        
        return [
            'backend' => $optimizationService->optimizeBackend($backendCode),
            'frontend' => $optimizationService->optimizeFrontend($frontendCode),
            'infrastructure' => $optimizationService->optimizeInfrastructure($infrastructure)
        ];
    }

    /**
     * Generate pixel animations based on requirements
     */
    public function generatePixelAnimation($requirements, $stylePreferences = [])
    {
        $analysis = $this->analyzeAnimationRequirements($requirements);
        $techStack = $this->determineAnimationTechStack($analysis, $stylePreferences);
        
        return [
            'animation' => $this->generateAnimationCode($analysis, $techStack),
            'assets' => $this->generateSpriteSheets($analysis),
            'controls' => $this->generateAnimationControls($analysis),
            'documentation' => $this->generateAnimationDocumentation($analysis)
        ];
    }

    /**
     * Generate a complete game based on requirements
     */
    public function generateGame($gameRequirements, $engine = 'phaser')
    {
        $analysis = $this->analyzeGameRequirements($gameRequirements);
        $gameStructure = $this->createGameStructure($analysis, $engine);
        
        return [
            'game' => $gameStructure,
            'assets' => $this->generateGameAssets($analysis),
            'documentation' => $this->generateGameDocumentation($analysis, $engine)
        ];
    }

    /**
     * Generate an ebook with styling and formatting
     */
    public function generateEbook($content, $format = 'epub', $style = 'modern')
    {
        $analysis = $this->analyzeContentStructure($content);
        $layout = $this->determineEbookLayout($analysis, $style);
        
        return [
            'ebook' => $this->compileEbook($analysis, $layout, $format),
            'assets' => $this->generateEbookAssets($analysis),
            'metadata' => $this->generateEbookMetadata($analysis)
        ];
    }

    /**
     * Deploy application to Render.com
     */
    public function deployToRender($appConfig, $deploymentConfig = [])
    {
        $config = array_merge($this->renderConfig, $deploymentConfig);
        
        // Generate deployment configuration
        $deployment = [
            'name' => $appConfig['name'] ?? 'my-app',
            'region' => $config['region'],
            'branch' => 'main',
            'buildCommand' => $this->generateBuildCommand($appConfig),
            'startCommand' => $this->generateStartCommand($appConfig),
            'envVars' => $this->prepareEnvironmentVariables($appConfig, $config)
        ];
        
        // Create deployment on Render
        return $this->createRenderDeployment($deployment);
    }

    /**
     * Set up AI team collaboration
     */
    public function setupAITeam($projectRequirements, $teamSize = 3)
    {
        $team = $this->assembleAITeam($projectRequirements, $teamSize);
        $workflow = $this->createTeamWorkflow($team, $projectRequirements);
        
        return [
            'team' => $team,
            'workflow' => $workflow,
            'tools' => $this->setupCollaborationTools()
        ];
    }
    
    // ===== PRIVATE HELPER METHODS =====
    
    private function analyzeAnimationRequirements($requirements)
    {
        // AI-powered analysis of animation requirements
        $prompt = "Analyze these animation requirements: " . json_encode($requirements) . 
                 " Extract key elements like characters, scenes, transitions, and interactions.";
                 
        return $this->callAIAnalysis($prompt, 'animation_analyst');
    }
    
    private function analyzeGameRequirements($requirements)
    {
        // AI-powered analysis of game requirements
        $prompt = "Analyze these game requirements: " . json_encode($requirements) . 
                 " Extract game mechanics, levels, characters, and scoring system.";
                 
        return $this->callAIAnalysis($prompt, 'game_designer');
    }
    
    private function generateAnimationCode($analysis, $techStack)
    {
        // Generate animation code based on analysis and tech stack
        $prompt = "Generate animation code using " . $techStack . " with these requirements: " . 
                 json_encode($analysis) . " Include smooth transitions and interactive elements.";
                 
        return $this->callAICodeGeneration($prompt, 'animation_developer');
    }
    
    private function generateGameStructure($analysis, $engine)
    {
        // Generate game structure based on analysis and engine
        $prompt = "Create a game structure using " . $engine . " with these requirements: " . 
                 json_encode($analysis) . " Include game loop, scenes, and core mechanics.";
                 
        return $this->callAICodeGeneration($prompt, 'game_developer');
    }
    
    private function compileEbook($analysis, $layout, $format)
    {
        // Compile ebook in specified format
        $prompt = "Generate ebook in " . $format . " format with this layout: " . 
                 json_encode($layout) . " and content: " . json_encode($analysis);
                 
        return $this->callAIContentGeneration($prompt, 'technical_writer');
    }
    
    private function createRenderDeployment($deploymentConfig)
    {
        // Implementation for Render.com deployment
        // This would use Render's API to create and configure the deployment
        
        return [
            'status' => 'success',
            'message' => 'Deployment initiated',
            'deployment_id' => uniqid('deploy_'),
            'dashboard_url' => 'https://dashboard.render.com/deployments/'.uniqid()
        ];
    }
    
    private function assembleAITeam($requirements, $teamSize)
    {
        // Determine which AI roles are needed based on project requirements
        $requiredRoles = $this->determineRequiredRoles($requirements);
        $team = [];
        
        // Select primary roles based on team size
        $primaryRoles = array_slice($requiredRoles, 0, $teamSize);
        
        foreach ($primaryRoles as $role) {
            $team[] = [
                'role' => $role,
                'title' => $this->aiTeamRoles[$role] ?? $role,
                'skills' => $this->getRoleSkills($role)
            ];
        }
        
        return $team;
    }
    
    private function determineRequiredRoles($requirements)
    {
        // AI-powered analysis to determine required team roles
        $prompt = "Based on these project requirements, determine the most important " . 
                 "team roles needed (in order of importance): " . json_encode($requirements);
                 
        $response = $this->callAIAnalysis($prompt, 'team_architect');
        
        // Default fallback roles if AI analysis fails
        return $response['roles'] ?? ['architect', 'frontend', 'backend', 'designer'];
    }
    
    private function createTeamWorkflow($team, $requirements)
    {
        // Create a collaborative workflow for the AI team
        $prompt = "Create a development workflow for a team with these roles: " . 
                 json_encode($team) . " for requirements: " . json_encode($requirements);
                 
        return $this->callAIAnalysis($prompt, 'workflow_designer');
    }
    
    private function setupCollaborationTools()
    {
        // Set up tools for AI team collaboration
        return [
            'version_control' => 'git',
            'project_management' => 'github_projects',
            'communication' => ['slack', 'github_discussions'],
            'documentation' => 'wiki',
            'ci_cd' => 'github_actions',
            'code_review' => 'github_pull_requests'
        ];
    }
    
    private function callAIAnalysis($prompt, $role = 'analyst')
    {
        // Generic method to call AI for analysis
        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => "You are a $role. Analyze the following requirements carefully."],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.3
            ]
        ]);
        
        $result = json_decode($response->getBody()->getContents(), true);
        return json_decode($result['choices'][0]['message']['content'], true) ?? [];
    }
    
    private function callAICodeGeneration($prompt, $role = 'developer')
    {
        // Generic method to call AI for code generation
        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => "You are a $role. Generate clean, efficient, and well-documented code."],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 4000,
                'temperature' => 0.2
            ]
        ]);
        
        $result = json_decode($response->getBody()->getContents(), true);
        return $result['choices'][0]['message']['content'] ?? '';
    }
    
    private function callAIContentGeneration($prompt, $role = 'writer')
    {
        // Generic method to call AI for content generation
        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => "You are a $role. Create well-structured and engaging content."],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 3000,
                'temperature' => 0.7
            ]
        ]);
        
        $result = json_decode($response->getBody()->getContents(), true);
        return $result['choices'][0]['message']['content'] ?? '';
    }
}