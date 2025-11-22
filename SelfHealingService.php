<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SelfHealingService
{
    protected $client;
    protected $projectPath;
    protected $errorLogs = [];
    protected $fixHistory = [];
    
    // Maximum number of automatic fix attempts
    protected $maxFixAttempts = 3;
    
    // Common error patterns and their potential fixes
    protected $errorPatterns = [
        // PHP Errors
        '/Call to undefined function (\w+)/' => [
            'type' => 'missing_function',
            'solution' => 'Check if the function exists in the current scope or if the extension is enabled.',
            'auto_fix' => 'check_function_availability'
        ],
        
        // JavaScript Errors
        '/Uncaught ReferenceError: (\w+) is not defined/' => [
            'type' => 'undefined_variable',
            'solution' => 'Variable is not defined in the current scope.',
            'auto_fix' => 'add_variable_definition'
        ],
        
        // Database Errors
        '/SQLSTATE\[\w+\] \[\d+\] (.*)/' => [
            'type' => 'database_error',
            'solution' => 'Check database connection and query syntax.',
            'auto_fix' => 'analyze_sql_query'
        ],
        
        // 404 Not Found
        '/404 Not Found/' => [
            'type' => 'route_not_found',
            'solution' => 'Check if the route is properly defined.',
            'auto_fix' => 'check_route_definition'
        ],
        
        // Permission Denied
        '/Permission denied/' => [
            'type' => 'permission_denied',
            'solution' => 'Check file/folder permissions.',
            'auto_fix' => 'fix_permissions'
        ]
    ];

    public function __construct(string $projectPath = null)
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false
        ]);
        
        $this->projectPath = $projectPath ?? base_path();
    }

    /**
     * Analyze error logs and attempt to fix issues
     */
    public function analyzeAndFix(string $errorLog, string $context = null): array
    {
        $this->errorLogs[] = [
            'timestamp' => now(),
            'error' => $errorLog,
            'context' => $context
        ];
        
        // Try to match error against known patterns
        $matchedPattern = null;
        $matches = [];
        
        foreach ($this->errorPatterns as $pattern => $info) {
            if (preg_match($pattern, $errorLog, $matches)) {
                $matchedPattern = $info;
                $matchedPattern['pattern'] = $pattern;
                $matchedPattern['matches'] = $matches;
                break;
            }
        }
        
        if (!$matchedPattern) {
            return [
                'success' => false,
                'message' => 'No matching error pattern found.',
                'suggestion' => 'Please check the error message and try again.'
            ];
        }
        
        // Attempt to fix the error
        $fixMethod = 'fix_' . $matchedPattern['auto_fix'];
        $fixResult = [];
        
        if (method_exists($this, $fixMethod)) {
            $fixResult = $this->$fixMethod($errorLog, $matchedPattern, $context);
            
            if ($fixResult['success']) {
                $this->fixHistory[] = [
                    'timestamp' => now(),
                    'error' => $errorLog,
                    'fix' => $fixResult,
                    'context' => $context
                ];
            }
            
            return $fixResult;
        }
        
        // If no specific fix method exists, return generic solution
        return [
            'success' => false,
            'message' => 'Error analysis complete.',
            'diagnosis' => $matchedPattern['type'],
            'solution' => $matchedPattern['solution'],
            'auto_fix_attempted' => false
        ];
    }
    
    /**
     * Check if a function exists and suggest fixes if not
     */
    protected function check_function_availability(string $error, array $patternInfo): array
    {
        $functionName = $patternInfo['matches'][1] ?? null;
        
        if (!$functionName) {
            return [
                'success' => false,
                'message' => 'Could not extract function name from error.'
            ];
        }
        
        // Check if it's a built-in function that needs an extension
        $extensionMap = [
            'mysqli_connect' => 'mysqli',
            'imagecreate' => 'gd',
            'curl_init' => 'curl',
            'json_encode' => 'json',
            'simplexml_load_string' => 'simplexml'
        ];
        
        if (isset($extensionMap[$functionName])) {
            $extension = $extensionMap[$functionName];
            
            // Try to enable the extension
            $process = new Process(['php', '-m']);
            $process->run();
            $loadedModules = $process->getOutput();
            
            if (strpos($loadedModules, $extension) === false) {
                // Extension not loaded, try to enable it
                $iniFile = php_ini_loaded_file();
                $iniContent = file_get_contents($iniFile);
                
                if (strpos($iniContent, "extension={$extension}") === false) {
                    // Add extension to php.ini
                    $iniContent .= "\nextension={$extension}";
                    
                    if (file_put_contents($iniFile, $iniContent) !== false) {
                        return [
                            'success' => true,
                            'message' => "Enabled PHP extension: {$extension}",
                            'action' => 'restart_web_server',
                            'requires_restart' => true
                        ];
                    }
                }
            }
        }
        
        // If we get here, we couldn't fix it automatically
        return [
            'success' => false,
            'message' => "Function {$functionName} is not available.",
            'solution' => 'Check if the required PHP extension is installed and enabled.',
            'auto_fix_attempted' => true
        ];
    }
    
    /**
     * Fix undefined JavaScript variables
     */
    protected function add_variable_definition(string $error, array $patternInfo): array
    {
        $variableName = $patternInfo['matches'][1] ?? null;
        
        if (!$variableName) {
            return [
                'success' => false,
                'message' => 'Could not extract variable name from error.'
            ];
        }
        
        // This is a simplified example - in a real implementation, you would:
        // 1. Parse the JavaScript file
        // 2. Find where the variable is being used
        // 3. Add the appropriate variable declaration
        
        return [
            'success' => false,
            'message' => "Undefined variable: {$variableName}",
            'solution' => "Add 'let', 'const', or 'var' before the variable name.",
            'auto_fix_attempted' => false
        ];
    }
    
    /**
     * Analyze and fix SQL queries
     */
    protected function analyze_sql_query(string $error, array $patternInfo): array
    {
        // Extract the SQL query from the error message
        $sqlError = $patternInfo['matches'][0] ?? $error;
        
        // This is a simplified example - in a real implementation, you would:
        // 1. Parse the SQL query
        // 2. Check for syntax errors
        // 3. Suggest fixes for common issues
        
        return [
            'success' => false,
            'message' => 'SQL Error Detected',
            'error' => $sqlError,
            'solution' => 'Review the SQL query for syntax errors or missing table/column references.',
            'auto_fix_attempted' => false
        ];
    }
    
    /**
     * Check and fix route definitions
     */
    protected function check_route_definition(string $error, array $patternInfo): array
    {
        // This is a simplified example - in a real implementation, you would:
        // 1. Parse the route configuration
        // 2. Check if the requested route exists
        // 3. Suggest the correct route or create it if possible
        
        return [
            'success' => false,
            'message' => 'Route Not Found',
            'solution' => 'Check if the route is defined in your routes file.',
            'auto_fix_attempted' => false
        ];
    }
    
    /**
     * Fix file/folder permissions
     */
    protected function fix_permissions(string $error, array $patternInfo): array
    {
        // Extract the file/folder path from the error message
        $path = $this->extractPathFromError($error);
        
        if ($path && file_exists($path)) {
            try {
                if (is_file($path)) {
                    chmod($path, 0644);
                } else {
                    chmod($path, 0755);
                }
                
                return [
                    'success' => true,
                    'message' => "Fixed permissions for: {$path}",
                    'action' => 'permissions_updated'
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => "Failed to update permissions: " . $e->getMessage(),
                    'solution' => 'Manually set the correct permissions for the file/folder.'
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Could not determine path from error message.',
            'solution' => 'Check the error message for the file/folder path and update permissions manually.'
        ];
    }
    
    /**
     * Extract file/folder path from error message
     */
    protected function extractPathFromError(string $error): ?string
    {
        // This is a simplified example - in a real implementation, you would
        // use more sophisticated pattern matching to extract the path
        if (preg_match("/in\s+([^\n:]+)/", $error, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }
    
    /**
     * Get error analysis history
     */
    public function getErrorHistory(): array
    {
        return $this->errorLogs;
    }
    
    /**
     * Get fix history
     */
    public function getFixHistory(): array
    {
        return $this->fixHistory;
    }
    
    /**
     * Clear error and fix history
     */
    public function clearHistory(): void
    {
        $this->errorLogs = [];
        $this->fixHistory = [];
    }
    
    /**
     * Run tests to verify fixes
     */
    public function runTests(): array
    {
        $process = new Process(['php', 'vendor/bin/phpunit']);
        $process->setWorkingDirectory($this->projectPath);
        
        try {
            $process->mustRun();
            $output = $process->getOutput();
            
            return [
                'success' => true,
                'message' => 'All tests passed!',
                'output' => $output
            ];
        } catch (ProcessFailedException $e) {
            $output = $e->getProcess()->getOutput();
            $errorOutput = $e->getProcess()->getErrorOutput();
            
            return [
                'success' => false,
                'message' => 'Tests failed',
                'output' => $output,
                'error' => $errorOutput
            ];
        }
    }
}
