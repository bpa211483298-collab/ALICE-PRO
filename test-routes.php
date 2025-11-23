<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-mcp', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Test route is working',
        'time' => now()->toDateTimeString(),
        'config' => [
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
        ]
    ]);
});
