<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Eloquent\AdRepository;
use App\Repositories\Eloquent\BaseRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\OptionRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\SourcingRepository;
use App\Repositories\Eloquent\GoogleSheetRepository;
use App\Repositories\Contracts\AdRepositoryInterface;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\OptionRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SourcingRepositoryInterface;
use App\Repositories\Contracts\GoogleSheetRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind(BaseRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(GoogleSheetRepositoryInterface::class, GoogleSheetRepository::class);
        $this->app->bind(AdRepositoryInterface::class, AdRepository::class);
        $this->app->bind(OptionRepositoryInterface::class, OptionRepository::class);
        $this->app->bind(SourcingRepositoryInterface::class, SourcingRepository::class);
    }
}
