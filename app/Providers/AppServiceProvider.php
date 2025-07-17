<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
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
        // Register User Observer
        // This handles automatic profile creation/deletion/restoration
        $this->registerUserObserver();
    }

    /**
     * Register the User Observer with conditional logic.
     */
    protected function registerUserObserver(): void
    {
        // Always register the observer
        // The observer itself handles when to skip actions
        User::observe(UserObserver::class);
    }
}
