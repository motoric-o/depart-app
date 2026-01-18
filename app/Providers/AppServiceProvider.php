<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Granular RBAC Gates
        \Illuminate\Support\Facades\Gate::define('manage-schedules', function ($user) {
            $user->loadMissing('accountType');
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Scheduling Admin']);
        });

        \Illuminate\Support\Facades\Gate::define('manage-routes', function ($user) {
            $user->loadMissing('accountType');
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Scheduling Admin']);
        });

        \Illuminate\Support\Facades\Gate::define('manage-buses', function ($user) {
            $user->loadMissing('accountType');
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Operations Admin']);
        });
        
        \Illuminate\Support\Facades\Gate::define('manage-users', function ($user) {
             $user->loadMissing('accountType');
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin']);
        });

        \Illuminate\Support\Facades\Gate::define('manage-drivers', function ($user) {
            $user->loadMissing('accountType');
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Operations Admin']);
        });

        // Booking Management
        \Illuminate\Support\Facades\Gate::define('manage-bookings', function ($user) {
            $user->loadMissing('accountType');
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Scheduling Admin']);
        });

        \Illuminate\Support\Facades\Gate::define('view-bookings', function ($user) {
            $user->loadMissing('accountType');
            // Fin (View), CS (Manage), Ops (View)
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Scheduling Admin', 'Financial Admin', 'Operations Admin']);
        });

        // Expense Management
        \Illuminate\Support\Facades\Gate::define('create-expense', function ($user) {
            $user->loadMissing('accountType');
            // Financial Admin needs Manage (Create) & Approve
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Operations Admin', 'Driver', 'Financial Admin']);
        });

        \Illuminate\Support\Facades\Gate::define('approve-expense', function ($user) {
            $user->loadMissing('accountType');
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Financial Admin']);
        });

        \Illuminate\Support\Facades\Gate::define('view-financial-reports', function ($user) {
            $user->loadMissing('accountType');
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Financial Admin']);
        });

        // View-Only Gates (For Dashboard & Index Pages)
        \Illuminate\Support\Facades\Gate::define('view-users', function ($user) {
            $user->loadMissing('accountType');
            // Ops (Manage Drivers), Sched (View Drivers)
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Operations Admin', 'Scheduling Admin']);
        });

        \Illuminate\Support\Facades\Gate::define('view-buses', function ($user) {
            $user->loadMissing('accountType');
            // Ops (Manage), Sched (View)
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Operations Admin', 'Scheduling Admin']);
        });

        \Illuminate\Support\Facades\Gate::define('view-routes', function ($user) {
            $user->loadMissing('accountType');
            // Sched (Manage), Ops (View)
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Operations Admin', 'Scheduling Admin']);
        });

        \Illuminate\Support\Facades\Gate::define('view-schedules', function ($user) {
            $user->loadMissing('accountType');
            // Sched (Manage), Ops (View)
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Operations Admin', 'Scheduling Admin']);
        });

        // Destination Gates
        \Illuminate\Support\Facades\Gate::define('manage-destinations', function ($user) {
            $user->loadMissing('accountType');
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Scheduling Admin']);
        });

        \Illuminate\Support\Facades\Gate::define('view-destinations', function ($user) {
            $user->loadMissing('accountType');
            // Same as routes/schedules + Financial?
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Scheduling Admin', 'Operations Admin', 'Financial Admin']);
        });
    }
}
