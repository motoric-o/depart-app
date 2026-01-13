<?php

use Illuminate\Support\Facades\Route;
use App\Models\Destination;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\OwnerController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    $destinations = Destination::orderBy('city_name')->get();
    return view('home', compact('destinations'));
});

Route::get('/schedules', [App\Http\Controllers\Web\SearchController::class, 'index'])->name('schedules.index');

Route::post('/chat', [App\Http\Controllers\ChatController::class, 'handle'])->name('chat');

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']); 
    Route::get('/signup', [AuthController::class, 'showSignupForm'])->name('signup');
    Route::post('/signup', [AuthController::class, 'register']);
    
    // Password Reset Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard Redirection
    // Dashboard Redirection
    Route::get('/dashboard', function () {
        $user = Auth::user();
        
        switch ($user->accountType->name) {
            case 'Admin':
                return redirect()->route('admin.dashboard');
            case 'Owner':
                return redirect()->route('owner.dashboard');
            default:
                $destinations = Destination::orderBy('city_name')->get();
                return view('home', compact('destinations'));
        }
    })->name('dashboard');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // ...
    });

    // Owner Routes
    Route::prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');
        
        // Users CRUD (Admins & Customers)
        Route::get('/users', [OwnerController::class, 'users'])->name('users');
        Route::get('/users/create', [OwnerController::class, 'createUser'])->name('users.create');
        Route::post('/users', [OwnerController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{id}/edit', [OwnerController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{id}', [OwnerController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [OwnerController::class, 'deleteUser'])->name('users.delete');

        // Revenue Reports
        Route::get('/reports', [OwnerController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [OwnerController::class, 'exportCsv'])->name('reports.export');
    });

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard'); // Optional explicit route
        // Users CRUD
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');

        // Buses CRUD
        Route::get('/buses', [AdminController::class, 'buses'])->name('buses');
        Route::get('/buses/create', [AdminController::class, 'createBus'])->name('buses.create');
        Route::post('/buses', [AdminController::class, 'storeBus'])->name('buses.store');
        Route::get('/buses/{id}/edit', [AdminController::class, 'editBus'])->name('buses.edit');
        Route::put('/buses/{id}', [AdminController::class, 'updateBus'])->name('buses.update');
        Route::delete('/buses/{id}', [AdminController::class, 'deleteBus'])->name('buses.delete');

        // Routes CRUD
        Route::get('/routes', [AdminController::class, 'routes'])->name('routes');
        Route::get('/routes/create', [AdminController::class, 'createRoute'])->name('routes.create');
        Route::post('/routes', [AdminController::class, 'storeRoute'])->name('routes.store');
        Route::get('/routes/{id}/edit', [AdminController::class, 'editRoute'])->name('routes.edit');
        Route::put('/routes/{id}', [AdminController::class, 'updateRoute'])->name('routes.update');
        Route::delete('/routes/{id}', [AdminController::class, 'deleteRoute'])->name('routes.delete');

        // Schedules CRUD (Maybe linked from Route, but global list is also fine)
        Route::get('/schedules', [AdminController::class, 'schedules'])->name('schedules');
        Route::get('/schedules/create', [AdminController::class, 'createSchedule'])->name('schedules.create');
        Route::post('/schedules', [AdminController::class, 'storeSchedule'])->name('schedules.store');
        Route::get('/schedules/{id}/edit', [AdminController::class, 'editSchedule'])->name('schedules.edit');
        Route::put('/schedules/{id}', [AdminController::class, 'updateSchedule'])->name('schedules.update');
        Route::delete('/schedules/{id}', [AdminController::class, 'deleteSchedule'])->name('schedules.delete');
    });
});
