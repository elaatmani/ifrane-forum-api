<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sourcing;
use App\Events\OrderUpdated;
use App\Events\OrderCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Observers\Order\OrderHistoryObserver;
use App\Observers\Order\OrderDeliveryObserver;
use App\Observers\Order\OrderFollowupObserver;
use App\Observers\Order\OrderItemHistoryObserver;
use App\Observers\Product\ProductHistoryObserver;
use App\Observers\Product\ProductVariantHistoryObserver;
use App\Listeners\SendOrderDetailsToExternalApi;
use App\Listeners\SendOrderStatusNotification;
use App\Listeners\SendNewOrderNotification;
use App\Observers\Sourcing\SourcingHistoryObserver;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OrderUpdated::class => [
            SendOrderDetailsToExternalApi::class,
            // SendOrderStatusNotification::class,
        ],
        OrderCreated::class => [
            // SendNewOrderNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Order
        Order::observe(OrderDeliveryObserver::class);
        Order::observe(OrderFollowupObserver::class);
        Order::observe(OrderHistoryObserver::class);
        OrderItem::observe(OrderItemHistoryObserver::class);

        // Product
        Product::observe(ProductHistoryObserver::class);
        ProductVariant::observe(ProductVariantHistoryObserver::class);

        // Sourcing
        Sourcing::observe(SourcingHistoryObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
