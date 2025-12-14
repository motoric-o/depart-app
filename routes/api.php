<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\BookingController;

// 1. Public Routes (No Login Required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Search trips using your Database View
Route::get('/trips/search', [ScheduleController::class, 'search']);
Route::get('/destinations', [ScheduleController::class, 'getDestinations']);

// 2. Protected Routes (Login Required)
// Note: Ensure you have Laravel Sanctum installed/configured for this middleware
Route::middleware('auth:sanctum')->group(function () {
    
    // User Profile
    Route::get('/user', [AuthController::class, 'me']);
    
    // Booking Flow
    Route::post('/bookings', [BookingController::class, 'store']); // The "Book Now" button
    Route::get('/my-bookings', [BookingController::class, 'index']); // Booking History
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']); // Cancel & Refund
});