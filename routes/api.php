<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DestinationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ScheduleDetailController;

// 1. Public Routes (No Login Required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ScheduleController Routes
Route::get('/schedules/search', [ScheduleController::class, 'search'])->name('api.schedules.search');
Route::get('/schedules/{id}/seats', [ScheduleController::class, 'getTakenSeats']);

// DestinationController Routes
Route::get('/destinations', [DestinationController::class, 'index']);

// 2. Protected Routes (Login Required)
// Note: Ensure you have Laravel Sanctum installed/configured for this middleware
Route::middleware('auth:sanctum')->group(function () {
    
    // User Profile
    Route::get('/user', [AuthController::class, 'me']);
    
    
    // Booking Flow
    Route::post('/bookings', [BookingController::class, 'store']); // The "Book Now" button
    Route::get('/my-bookings', [BookingController::class, 'index']); // Booking History
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']); // Cancel & Refund

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
});

// Schedule Details (Admin - Pilot Feature) - Using 'web' middleware to share Admin Session
// Schedule Details (Admin - Pilot Feature) - Using 'web' middleware to share Admin Session
Route::middleware(['web', 'auth', 'role:Owner,Super Admin,Financial Admin,Scheduling Admin,Operations Admin'])
    ->name('api.') // Namespace these routes to avoid name collisions (e.g. schedules.index)
    ->group(function () {
    // Phase 2: Refactored Admin API
    Route::apiResource('/admin/schedules', \App\Http\Controllers\Api\Admin\ScheduleController::class);
    Route::apiResource('/admin/buses', \App\Http\Controllers\Api\Admin\BusController::class);
    Route::apiResource('/admin/routes', \App\Http\Controllers\Api\Admin\RouteController::class);
    Route::apiResource('/admin/users', \App\Http\Controllers\Api\Admin\UserController::class);

    Route::get('/schedules/{id}/details', [ScheduleDetailController::class, 'index']);
    Route::post('/schedules/{id}/details', [ScheduleDetailController::class, 'store']); // Create manual entry
    Route::put('/schedules/details/{detail_id}', [ScheduleDetailController::class, 'update']);
    Route::delete('/schedules/details/{detail_id}', [ScheduleDetailController::class, 'destroy']);

    // Phase 5: Expense Management
    Route::get('/admin/expenses', [\App\Http\Controllers\Api\Admin\ExpenseController::class, 'index']);
    Route::post('/admin/expenses', [\App\Http\Controllers\Api\Admin\ExpenseController::class, 'store']);
    Route::put('/admin/expenses/{id}', [\App\Http\Controllers\Api\Admin\ExpenseController::class, 'update']);
    Route::put('/admin/expenses/{id}/verify', [\App\Http\Controllers\Api\Admin\ExpenseController::class, 'verify']);
    Route::delete('/admin/expenses/{id}', [\App\Http\Controllers\Api\Admin\ExpenseController::class, 'destroy']);
});