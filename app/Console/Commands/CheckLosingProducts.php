<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckLosingProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-losing-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for losing products and notify admins';

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(NotificationService $notificationService, ProductRepositoryInterface $productRepository)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
        $this->productRepository = $productRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for losing products...');
        
        try {
            // Get all products with their profit data
            $productsData = $this->productRepository->topProducts([
                'per_page' => 1000, // Use a large number to get all products
            ]);
            
            // Filter products with total_profit < 0
            $losingProducts = collect($productsData['data'])
                ->filter(function ($product) {
                    return $product->total_profit < 0;
                })
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'total_profit' => $product->total_profit
                    ];
                })
                ->values()
                ->toArray();
            
            $this->info('Found ' . count($losingProducts) . ' losing products.');
            
            // If you have losing products, send notification
            if (count($losingProducts) > 0) {
                $this->sendLosingProductsNotification($losingProducts);
            }
            
            $this->info('Completed checking for losing products.');
            
            return 0;
        } catch (\Exception $e) {
            Log::error('Error checking for losing products: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Send notification for losing products.
     */
    protected function sendLosingProductsNotification(array $products)
    {
        $title = count($products) > 1 
            ? 'تم اكتشاف ' . count($products) . ' منتجات خاسرة'
            : 'تم اكتشاف منتج خاسر';
            
        $message = count($products) > 1
            ? 'تم تحديد العديد من المنتجات على أنها منتجات خاسرة.'
            : 'تم تحديد منتج على أنه منتج خاسر.';
        
        // Get admin users - only use 'admin' role
        $admins = \App\Models\User::role(['admin'])->get();
        
        if ($admins->isEmpty()) {
            // If no admins found, use user ID 1
            $user = \App\Models\User::find(1);
            if ($user) {
                $admins = collect([$user]);
            }
        }
        
        if (!$admins->isEmpty()) {
            $this->notificationService->sendSystemNotification(
                $title,
                $message,
                [
                    'products' => $products,
                    'type' => 'losing_products'
                ],
                'warning',
                $admins->all()
            );
        }
        
        $this->info('Sent losing products notification for ' . count($products) . ' products.');
    }
} 