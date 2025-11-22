<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed process(string $input, array $context = [], string $inputType = 'text', array $requirements = [])
 * @method static mixed optimizeInput(string $input, string $taskType = 'general', array $context = [])
 * @method static mixed formatOutput($response, string $formatType = 'default')
 * 
 * @see \App\Services\AIOrchestrationService
 */
class AI extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ai';
    }
}
