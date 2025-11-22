<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\AdvancedCodeGenerationService;

/**
 * Sample Project: Pixel Art Animation Studio
 * 
 * This example demonstrates how to use the AdvancedCodeGenerationService
 * to create a complete pixel art animation studio with deployment to Render.com
 */

// Initialize the service
$service = new AdvancedCodeGenerationService();

// 1. Define project requirements
$requirements = [
    'name' => 'PixelArt Studio',
    'description' => 'An online platform for creating and sharing pixel art animations',
    'features' => [
        'user authentication',
        'pixel art editor',
        'animation timeline',
        'sprite sheet generation',
        'gallery',
        'social sharing'
    ],
    'tech_stack' => [
        'frontend' => 'react',
        'backend' => 'node',
        'database' => 'mongodb',
        'animation' => 'pixijs',
        'styling' => 'tailwind'
    ]
];

// 2. Generate the application
$app = $service->generateCompleteApplication($requirements);

// 3. Add pixel animation features
$animationFeatures = [
    'canvas_size' => '32x32',
    'palette' => [
        'type' => 'custom',
        'colors' => [
            '#000000', '#FFFFFF', '#FF0000', '#00FF00', '#0000FF',
            '#FFFF00', '#00FFFF', '#FF00FF', '#808080', '#800000',
            '#808000', '#008000', '#800080', '#008080', '#000080'
        ]
    ],
    'tools' => ['pencil', 'eraser', 'fill', 'color_picker', 'selection'],
    'animation' => [
        'frames' => 8,
        'fps' => 12,
        'loop' => true
    ]
];

$animationModule = $service->generatePixelAnimation($animationFeatures);
$app['frontend_code']['components']['PixelEditor'] = $animationModule['animation'];
$app['frontend_code']['assets']['sprites'] = $animationModule['assets'];

// 4. Set up deployment configuration
$deploymentConfig = [
    'name' => 'pixelart-studio',
    'region' => 'oregon',
    'auto_deploy' => true,
    'env_vars' => [
        'NODE_ENV' => 'production',
        'PORT' => '10000',
        'MONGODB_URI' => '${MONGO_URI}',  // Will be set in Render.com dashboard
        'JWT_SECRET' => '${JWT_SECRET}',  // Will be set in Render.com dashboard
        'CLOUDINARY_URL' => '${CLOUDINARY_URL}'  // For image storage
    ]
];

// 5. Deploy to Render.com
try {
    $deployment = $service->deployToRender($app, $deploymentConfig);
    
    echo "🎉 Deployment initiated successfully!\n";
    echo "🔗 Dashboard: " . $deployment['dashboard_url'] . "\n";
    echo "⏳ Status: " . $deployment['status'] . "\n\n";
    
    // 6. Set up AI team for ongoing development
    $team = $service->setupAITeam([
        'type' => 'saas',
        'scale' => 'startup',
        'technologies' => ['react', 'node', 'mongodb', 'pixijs'],
        'features' => ['user management', 'real-time collaboration', 'asset management']
    ], 4);
    
    echo "👥 AI Team Assembled:\n";
    foreach ($team['team'] as $member) {
        echo "- " . $member['title'] . " (" . $member['role'] . ")\n";
    }
    
    echo "\n🚀 Project setup complete! Your pixel art studio will be available shortly.\n";
    
} catch (\Exception $e) {
    echo "❌ Deployment failed: " . $e->getMessage() . "\n";
    
    // Save generated code locally for debugging
    file_put_contents(__DIR__ . '/pixelart-studio-generated-code.json', 
        json_encode($app, JSON_PRETTY_PRINT)
    );
    
    echo "💾 Generated code saved to pixelart-studio-generated-code.json\n";
}

// 7. Generate documentation
$documentation = [
    'project_name' => 'PixelArt Studio',
    'description' => 'Documentation for the PixelArt Studio project',
    'sections' => [
        'getting_started' => 'Setup and installation instructions',
        'features' => 'Detailed feature documentation',
        'api' => 'API endpoints and usage',
        'deployment' => 'Deployment and hosting instructions',
        'troubleshooting' => 'Common issues and solutions'
    ]
];

$docs = $service->generateEbook($documentation, 'md', 'github');
file_put_contents(__DIR__ . '/DOCUMENTATION.md', $docs['ebook']);

echo "📚 Documentation generated: " . __DIR__ . "/DOCUMENTATION.md\n";
