<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductStockAlert;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckLowStockProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-low-stock-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for products with low stock and notify admins';

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        NotificationService $notificationService
    ) {
        parent::__construct();
        $this->productRepository = $productRepository;
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for products with low stock...');
        
        try {
            // Get all active products
            $products = Product::where('is_active', true)->get();
            $lowStockProducts = [];
            $recoveredProducts = [];
            
            foreach ($products as $product) {
                // Skip products without stock alert threshold
                if (!$product->stock_alert) {
                    continue;
                }
                
                // Get available quantity
                $availableQuantity = $this->productRepository->getAvailableQuantityForProduct($product->id);
                
                // Check if product has low stock
                $hasLowStock = $availableQuantity <= $product->stock_alert;
                
                // Get existing alert for this product
                $existingAlert = ProductStockAlert::where('product_id', $product->id)
                    ->active()
                    ->first();
                
                if ($hasLowStock) {
                    // Product has low stock
                    if (!$existingAlert) {
                        // Create new alert and add to notification list
                        ProductStockAlert::create([
                            'product_id' => $product->id,
                            'notified_at' => now(),
                        ]);
                        
                        $lowStockProducts[] = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'available' => $availableQuantity,
                            'threshold' => $product->stock_alert
                        ];
                    }
                } else {
                    // Product has recovered from low stock
                    if ($existingAlert) {
                        // Mark alert as resolved
                        $existingAlert->update(['resolved_at' => now()]);
                        
                        $recoveredProducts[] = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'available' => $availableQuantity
                        ];
                    }
                }
            }
            
            // Send notifications for low stock products
            if (count($lowStockProducts) > 0) {
                $this->sendLowStockNotification($lowStockProducts);
            }
            
            // Send notifications for recovered products
            if (count($recoveredProducts) > 0) {
                $this->sendStockRecoveredNotification($recoveredProducts);
            }
            
            $this->info('Completed: ' . count($lowStockProducts) . ' new low stock alerts, ' . 
                count($recoveredProducts) . ' products recovered.');
            
            return 0;
        } catch (\Exception $e) {
            Log::error('Error checking for low stock products: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Send notification for products with low stock.
     */
    protected function sendLowStockNotification(array $products)
    {
        $title = count($products) > 1 
            ? 'نفاذ مخزون ' . count($products) . ' منتجات' 
            : 'نفاذ مخزون منتج';
            
        $message = count($products) > 1
            ? 'العديد من المنتجات على وشك نفاذ المخزون وتتطلب اهتمامك.'
            : 'منتج على وشك نفاذ المخزون ويتطلب اهتمامك.';
        
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
                    'type' => 'low_stock'
                ],
                'warning',
                $admins->all()
            );
        }
        
        $this->info('Sent low stock notification for ' . count($products) . ' products.');
    }
    
    /**
     * Send notification for products that have recovered from low stock.
     */
    protected function sendStockRecoveredNotification(array $products)
    {
        $title = count($products) > 1 
            ? count($products) . ' منتجات متوفرة في المخزون' 
            : 'منتج متوفر في المخزون';
            
        $message = count($products) > 1
            ? 'العديد من المنتجات قد تعافت من حالة نفاذ المخزون.'
            : 'منتج قد تعافى من حالة نفاذ المخزون.';
        
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
                    'type' => 'stock_recovered'
                ],
                'success',
                $admins->all()
            );
        }
        
        $this->info('Sent stock recovered notification for ' . count($products) . ' products.');
    }
} 