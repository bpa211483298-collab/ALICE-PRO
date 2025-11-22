<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\AdvancedCodeGenerationService;
use App\Services\ProjectTemplates;

// Example 1: List all available templates
$templates = ProjectTemplates::getAvailableTemplates();
echo "Available Templates:\n";
foreach ($templates as $id => $name) {
    echo "- {$id}: {$name}\n";}

// Example 2: Generate a new Next.js application
$requirements = [
    'name' => 'My Next.js App',
    'description' => 'A modern web application built with Next.js',
    'features' => [
        'Server-side rendering',
        'API routes',
        'Image optimization'
    ]
];

$preferences = [
    'author' => 'John Doe',
    'version' => '1.0.0',
    'output_dir' => __DIR__ . '/generated/nextjs-app'
];

$generator = new AdvancedCodeGenerationService();

// This will automatically use the Next.js template
try {
    $result = $generator->generateCompleteApplication($requirements, $preferences);
    
    echo "\nProject generated successfully!\n";
    echo "Location: " . $result['target_directory'] . "\n";
    echo "Template used: " . $result['template']['name'] . "\n\n";
    
    echo "Next steps:\n";
    foreach ($result['next_steps'] as $step) {
        echo "- {$step}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
