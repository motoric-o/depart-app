<?php

use Illuminate\Support\Facades\Route;
use App\Models\Destination;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\OwnerController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    $destinations = Destination::orderBy('city_name', 'asc')->get();
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
    Route::get('/dashboard', function () {
        $user = Auth::user();
        
        switch ($user->accountType->name) {
            case 'Admin':
                return redirect()->route('admin.dashboard');
            case 'Owner':
                return redirect()->route('owner.dashboard');
            case 'Driver':
                return redirect()->route('driver.dashboard');
            default:
        $roleValues = ['Owner', 'Super Admin', 'Financial Admin', 'Scheduling Admin', 'Operations Admin'];
        if (in_array($user->accountType->name, $roleValues)) {
            return redirect()->route('admin.dashboard'); // Re-use Admin dashboard but content varies by Gates
        }
        if ($user->accountType->name === 'Driver') {
             // Driver dashboard or profile
             return redirect()->route('profile.edit'); 
        }
        
        return view('customer.dashboard');
        }
    })->name('dashboard');

    // Customer Booking Routes
    Route::get('/booking/create', [\App\Http\Controllers\Web\BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking', [\App\Http\Controllers\Web\BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/{booking_id}/payment', [\App\Http\Controllers\Web\BookingController::class, 'payment'])->name('booking.payment');
    Route::post('/booking/{booking_id}/complete', [\App\Http\Controllers\Web\BookingController::class, 'completePayment'])->name('booking.complete');
    Route::get('/booking/{booking_id}/ticket', [\App\Http\Controllers\Web\BookingController::class, 'ticket'])->name('booking.ticket');
    Route::get('/booking/history', [\App\Http\Controllers\Web\BookingController::class, 'history'])->name('booking.history');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // ...
    });

    // Owner Routes
    Route::prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');
        
        // Users Management unified under Admin Routes


        // Revenue Reports
        Route::get('/reports', [OwnerController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [OwnerController::class, 'exportCsv'])->name('reports.export');

    });

    // Driver Routes
    Route::prefix('driver')->name('driver.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Web\DriverController::class, 'dashboard'])->name('dashboard');
        Route::get('/schedules/{id}', [App\Http\Controllers\Web\DriverController::class, 'schedule'])->name('schedules.show');
        Route::post('/schedules/{id}/remarks', [App\Http\Controllers\Web\DriverController::class, 'updateRemarks'])->name('schedules.remarks');
        Route::post('/seats/{id}/check-in', [App\Http\Controllers\Web\DriverController::class, 'checkInSeat'])->name('seats.check-in');
        
        Route::get('/expenses', [App\Http\Controllers\Web\DriverController::class, 'expenses'])->name('expenses');
        Route::post('/expenses', [App\Http\Controllers\Web\DriverController::class, 'storeExpense'])->name('expenses.store');
        Route::post('/expenses/{id}/confirm', [App\Http\Controllers\Web\DriverController::class, 'confirmExpense'])->name('expenses.confirm');
        Route::post('/expenses/{id}/issue', function (Illuminate\Http\Request $request, $id) {
             \Illuminate\Support\Facades\Log::info("Hit issue route for ID: $id");
             return app(App\Http\Controllers\Web\DriverController::class)->reportExpenseIssue($request, $id);
        })->name('expenses.issue');
        
        Route::get('/earnings', [App\Http\Controllers\Web\DriverController::class, 'earnings'])->name('earnings');
    });

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('role:Owner,Super Admin,Financial Admin,Scheduling Admin,Operations Admin')->group(function () {
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

        // Bookings CRUD
        Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
        // Route::get('/bookings/{id}/details', ...); // If we add details page later
        Route::delete('/bookings/{id}', [AdminController::class, 'deleteBooking'])->name('bookings.delete');

        // Schedules CRUD (Maybe linked from Route, but global list is also fine)
        Route::get('/schedules', [AdminController::class, 'schedules'])->name('schedules');
        Route::get('/schedules/create', [AdminController::class, 'createSchedule'])->name('schedules.create');
        Route::post('/schedules', [AdminController::class, 'storeSchedule'])->name('schedules.store');
        Route::get('/schedules/{id}/edit', [AdminController::class, 'editSchedule'])->name('schedules.edit');
        Route::put('/schedules/{id}', [AdminController::class, 'updateSchedule'])->name('schedules.update');
        Route::delete('/schedules/{id}', [AdminController::class, 'deleteSchedule'])->name('schedules.delete');
        
        // Schedule Details (View Shell)
        Route::get('/schedules/{id}/details', [AdminController::class, 'scheduleDetails'])->name('schedules.details');

        // Expenses
        Route::get('/expenses', [AdminController::class, 'expenses'])->name('expenses');
        Route::get('/expenses/create', [AdminController::class, 'createExpense'])->name('expenses.create');
        Route::post('/expenses', [AdminController::class, 'storeExpense'])->name('expenses.store');
        Route::post('/expenses/{id}/verify', [AdminController::class, 'verifyExpense'])->name('expenses.verify');

        // Transactions
        Route::get('/transactions', [AdminController::class, 'transactions'])->name('transactions');
        Route::post('/transactions/{id}/resolve', [AdminController::class, 'resolveTransactionIssue'])->name('transactions.resolve');
        Route::get('/transactions/export', [AdminController::class, 'exportTransactions'])->name('transactions.export');

        // Financial Reports
        Route::get('/financial-reports', [AdminController::class, 'financialReports'])->name('financial.reports');
    });
});