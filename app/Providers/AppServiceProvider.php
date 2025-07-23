<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Services\Community\CompanyDataService;
use App\Services\Community\ConnectionService;
use App\Services\Community\UserRecommendationService;
use App\Services\Community\CommunityMemberService;
use App\Services\Community\CompanyRecommendationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerCommunityServices();
    }

    /**
     * Register community-related services.
     */
    protected function registerCommunityServices(): void
    {
        $this->app->singleton(CompanyDataService::class);
        $this->app->singleton(ConnectionService::class);
        $this->app->singleton(UserRecommendationService::class);
        $this->app->singleton(CommunityMemberService::class);
        $this->app->singleton(CompanyRecommendationService::class);
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
