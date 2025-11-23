<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Use array driver for all tests
        config(['database.default' => 'array']);
        
        // Set up the array connection
        config(['database.connections.array' => [
            'driver' => 'array',
        ]]);
    }
}
