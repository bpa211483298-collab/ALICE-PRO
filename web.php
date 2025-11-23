<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:admin|super-admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Add more admin routes here
    Route::get('/users', function () {
        return Inertia::render('Admin/Users/Index');
    })->name('users.index');
    
    Route::get('/settings', function () {
        return Inertia::render('Admin/Settings');
    })->name('settings');
});

// Regular User Dashboard
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Social Authentication Routes
Route::controller(\App\Http\Controllers\Auth\SocialAuthController::class)
    ->group(function () {
        Route::get('auth/{provider}/redirect', 'redirectToProvider')
            ->name('social.redirect');
        Route::get('auth/{provider}/callback', 'handleProviderCallback')
            ->name('social.callback');
    });

// Root route - redirect to dashboard if authenticated, otherwise to login
Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});
