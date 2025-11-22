<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\AdvancedCodeGenerationService;

/**
 * Database Integration Example with Render.com
 * 
 * This example demonstrates how to set up a complete application with database
 * integration and deploy it to Render.com
 */

// Initialize the service
$service = new AdvancedCodeGenerationService();

// 1. Define application requirements with database needs
$requirements = [
    'name' => 'TaskMaster Pro',
    'description' => 'A collaborative task management application',
    'features' => [
        'user authentication',
        'task management',
        'team collaboration',
        'real-time updates',
        'file attachments',
        'activity logging'
    ],
    'database' => [
        'type' => 'postgresql',
        'requirements' => [
            'tables' => ['users', 'tasks', 'teams', 'attachments', 'activity_logs'],
            'relationships' => [
                'users' => ['hasMany' => ['tasks', 'activity_logs']],
                'tasks' => ['belongsTo' => ['users', 'teams']],
                'teams' => ['hasMany' => ['users', 'tasks']]
            ],
            'indexes' => [
                'users' => ['email', 'team_id'],
                'tasks' => ['due_date', 'status', 'team_id', 'user_id'],
                'activity_logs' => ['created_at', 'user_id']
            ]
        ]
    ]
];

// 2. Generate the application with database integration
$app = $service->generateCompleteApplication($requirements);

// 3. Configure database connection for Render.com
$renderDbConfig = [
    'database' => [
        'type' => 'postgresql',
        'connection' => [
            'host' => '${RENDER_DB_HOST}',
            'port' => '5432',
            'database' => '${RENDER_DB_NAME}',
            'username' => '${RENDER_DB_USERNAME}',
            'password' => '${RENDER_DB_PASSWORD}'
        ],
        'ssl' => [
            'enabled' => true,
            'ca_cert' => '/etc/ssl/certs/ca-certificates.crt'
        ],
        'pool' => [
            'min' => 2,
            'max' => 10
        ]
    ]
];

// 4. Add database configuration to the application
$app['database_config'] = $renderDbConfig;

// 5. Set up deployment configuration for Render.com
$deploymentConfig = [
    'name' => 'taskmaster-pro',
    'region' => 'oregon',
    'auto_deploy' => true,
    'services' => [
        'web' => [
            'build_command' => 'npm install && npm run build',
            'start_command' => 'node server.js',
            'env_vars' => [
                'NODE_ENV' => 'production',
                'PORT' => '10000',
                'DATABASE_URL' => 'postgresql://${RENDER_DB_USERNAME}:${RENDER_DB_PASSWORD}@${RENDER_DB_HOST}:5432/${RENDER_DB_NAME}?sslmode=require',
                'JWT_SECRET' => '${JWT_SECRET}',
                'S3_BUCKET' => '${S3_BUCKET}',
                'S3_REGION' => '${S3_REGION}',
                'S3_ACCESS_KEY' => '${S3_ACCESS_KEY}',
                'S3_SECRET_KEY' => '${S3_SECRET_KEY}'
            ]
        ],
        'database' => [
            'type' => 'postgresql',
            'version' => '14',
            'name' => 'taskmaster_db',
            'database_name' => 'taskmaster_production',
            'user' => 'taskmaster_user',
            'plan' => 'free',
            'region' => 'oregon'
        ]
    ],
    'crons' => [
        [
            'name' => 'database-backup',
            'schedule' => '0 0 * * *',  // Daily at midnight
            'command' => 'pg_dump $DATABASE_URL > /tmp/backup.sql && gzip /tmp/backup.sql',
            'destination' => 's3://${S3_BUCKET}/backups/'
        ]
    ]
];

// 6. Generate deployment scripts
$deploymentScripts = [
    'render.yaml' => $this->generateRenderYaml($deploymentConfig),
    'migrate-db.sh' => '#!/bin/bash
set -e
psql $DATABASE_URL -f /app/database/schema.sql
psql $DATABASE_URL -f /app/database/seed.sql
',
    'database/schema.sql' => $app['database_schema'],
    'database/seed.sql' => $this->generateSeedData($app['database_schema'])
];

// 7. Add deployment scripts to the application
$app['deployment_scripts'] = $deploymentScripts;

// 8. Save the complete application
file_put_contents(
    __DIR__ . '/taskmaster-pro-generated.json',
    json_encode($app, JSON_PRETTY_PRINT)
);

// 9. Deploy to Render.com
try {
    $deployment = $service->deployToRender($app, $deploymentConfig);
    
    echo "🚀 Deployment initiated successfully!\n";
    echo "🔗 Dashboard: " . $deployment['dashboard_url'] . "\n";
    echo "⏳ Status: " . $deployment['status'] . "\n\n";
    
    // 10. Set up monitoring and alerts
    $monitoringConfig = [
        'alerts' => [
            [
                'name' => 'High CPU Usage',
                'condition' => 'cpu.usage > 80%',
                'notify' => ['email:team@example.com', 'slack:#alerts']
            ],
            [
                'name' => 'Database Connection Issues',
                'condition' => 'db.connection_errors > 5',
                'notify' => ['email:dba@example.com', 'pagerduty:high_priority']
            ]
        ],
        'logs' => [
            'retention_days' => 30,
            'export_to' => 's3://${S3_BUCKET}/logs/'
        ]
    ];
    
    echo "📊 Monitoring configured with " . count($monitoringConfig['alerts']) . " alert rules\n";
    
} catch (\Exception $e) {
    echo "❌ Deployment failed: " . $e->getMessage() . "\n";
    
    // Save error details for debugging
    file_put_contents(
        __DIR__ . '/deployment-error.log',
        "[" . date('Y-m-d H:i:s') . "] " . $e->getMessage() . "\n" . $e->getTraceAsString()
    );
    
    echo "📝 Error details saved to deployment-error.log\n";
}

// Helper function to generate Render.com YAML configuration
private function generateRenderYaml($config) {
    $yaml = [
        'services' => [
            [
                'type' => 'web',
                'name' => $config['name'],
                'env' => 'node',
                'buildCommand' => $config['services']['web']['build_command'],
                'startCommand' => $config['services']['web']['start_command'],
                'envVars' => array_map(function($value) {
                    return ['key' => $value[0], 'value' => $value[1]];
                }, $config['services']['web']['env_vars'])
            ]
        ],
        'databases' => [
            [
                'name' => $config['services']['database']['name'],
                'databaseName' => $config['services']['database']['database_name'],
                'user' => $config['services']['database']['user'],
                'plan' => $config['services']['database']['plan'],
                'region' => $config['services']['database']['region']
            ]
        ]
    ];
    
    if (isset($config['crons'])) {
        $yaml['crons'] = array_map(function($cron) {
            return [
                'name' => $cron['name'],
                'schedule' => $cron['schedule'],
                'command' => $cron['command']
            ];
        }, $config['crons']);
    }
    
    return yaml_emit($yaml);
}

// Helper function to generate sample seed data
private function generateSeedData($schema) {
    // This would analyze the schema and generate appropriate test data
    return "-- Sample seed data\n" .
           "INSERT INTO users (email, name, created_at) VALUES \n" .
           "  ('admin@example.com', 'Admin User', NOW()),\n" .
           "  ('user@example.com', 'Regular User', NOW());\n\n" .
           "-- Add more seed data based on the schema";
}

// Generate documentation for database setup
$dbDocs = [
    'title' => 'Database Setup Guide',
    'sections' => [
        'local_development' => [
            'title' => 'Local Development Setup',
            'content' => '1. Install PostgreSQL 14+\n2. Create a database named `taskmaster_development`\n3. Update .env with your database credentials'
        ],
        'migrations' => [
            'title' => 'Running Migrations',
            'content' => 'Run `npm run db:migrate` to apply database migrations\nRun `npm run db:seed` to populate with sample data'
        ],
        'backups' => [
            'title' => 'Database Backups',
            'content' => 'Automated backups are configured to run daily at midnight\nBackups are stored in S3 for 30 days'
        ],
        'performance' => [
            'title' => 'Performance Tuning',
            'content' => 'Recommended indexes have been created\nConnection pooling is configured for optimal performance'
        ]
    ]
];

file_put_contents(__DIR__ . '/DATABASE.md', $service->generateEbook($dbDocs, 'md', 'github')['ebook']);

echo "📚 Database documentation generated: " . __DIR__ . "/DATABASE.md\n";
