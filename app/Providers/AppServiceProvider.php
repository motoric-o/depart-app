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

        // Expense Management
        \Illuminate\Support\Facades\Gate::define('create-expense', function ($user) {
            $user->loadMissing('accountType');
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Operations Admin', 'Driver']);
        });

        \Illuminate\Support\Facades\Gate::define('approve-expense', function ($user) {
            $user->loadMissing('accountType');
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Financial Admin']);
        });

        \Illuminate\Support\Facades\Gate::define('view-financial-reports', function ($user) {
            $user->loadMissing('accountType');
            return $user->accountType && in_array($user->accountType->name, ['Owner', 'Super Admin', 'Financial Admin']);
        });
    }
}
