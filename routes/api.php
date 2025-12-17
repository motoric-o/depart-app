<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DestinationController;
use App\Http\Controllers\Api\NotificationController;

// 1. Public Routes (No Login Required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ScheduleController Routes
Route::get('/schedules/search', [ScheduleController::class, 'search']);
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