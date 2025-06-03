<?php

namespace App\Http\Controllers\Sourcing;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Sourcing;
use App\Models\SourcingVariant;
use App\Repositories\Eloquent\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SourcingArrivedController extends Controller
{
    protected $productRepository;

    /**
     * Create a new controller instance.
     *
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->all();

            // Find the sourcing record to determine type
            $sourcing = Sourcing::with('variants')->findOrFail($validated['id']);
            
            // Process based on sourcing type
            if ($sourcing->sourcing_type == 'new_product') {
                return $this->processNewProductSourcing($sourcing, $validated);
            } elseif ($sourcing->sourcing_type == 'restock') {
                return $this->processRestockSourcing($sourcing, $validated);
            }

            return response()->json([
                'message' => 'Invalid sourcing type',
                'data' => $sourcing
            ], 400);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error processing sourcing arrived webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error processing webhook',
                'line' => $e->getLine(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a new product sourcing
     */
    private function processNewProductSourcing(Sourcing $sourcing, array $validated)
    {
        // Start transaction
        DB::beginTransaction();

        try {
            // Prepare product data
            $productData = [
                'sku' => 's-' . Str::lower(Str::random(3)),
                'name' => $validated['product_name'],
                'description' => $sourcing->note,
                'buying_price' => $validated['buying_price'],
                'selling_price' => $validated['selling_price'],
                'is_active' => true,
                'has_variants' => count($sourcing->variants) > 0,
                'quantity' => $validated['quantity'],
                'store_url' => $validated['product_url'],
                'created_by' => $sourcing->created_by,
            ];

            // Prepare variants data
            $variants = [];
            if (count($sourcing->variants) > 0) {
                foreach ($sourcing->variants as $variant) {
                    $variants[] = [
                        'variant_name' => $variant->variant_name,
                        'quantity' => $variant->quantity,
                    ];
                }
            }

            // Create product using repository (will trigger ProductHistoryObserver)
            $product = $this->productRepository->create($productData, $variants);

            // Update sourcing record (will trigger SourcingHistoryObserver)
            $sourcing->update([
                'status' => 'completed',
                'product_id' => $product->id,
                'product_name' => $validated['product_name'],
                'selling_price' => $validated['selling_price'], 
                'buying_price' => $validated['buying_price'],
                'product_url' => $validated['product_url'],
                'quantity' => $validated['quantity'],
                'weight' => $validated['weight'],
                'destination_country' => $validated['destination_country'],
                'shipping_method' => $validated['shipping_method'],
                'shipping_cost' => $validated['shipping_cost'],
                'cost_per_unit' => $validated['cost_per_unit'],
                'additional_fees' => $validated['additional_fees'],
            ]);

            DB::commit();

            Log::info('New product sourcing arrived processed successfully', [
                'sourcing_id' => $sourcing->id,
                'product_id' => $product->id
            ]);

            return response()->json([
                'message' => 'New product sourcing processed successfully',
                'code' => 'SUCCESS',
                'data' => [
                    'sourcing' => $sourcing,
                    'product' => $product
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process a restock sourcing
     */
    private function processRestockSourcing(Sourcing $sourcing, array $validated)
    {
        // Start transaction
        DB::beginTransaction();

        try {
            // Find the product to update
            $product = $this->productRepository->find($sourcing->product_id);
            $oldProductQuantity = $product->quantity;
            
            // Update product data with price information
            $productData = [];
            $productVariants = [];
            
            // Check if the product has variants
            if (count($sourcing->variants) > 0) {
                // Product has variants - update each variant quantity
                foreach ($sourcing->variants as $sourcingVariant) {
                    if ($sourcingVariant->product_variant_id) {
                        $productVariant = ProductVariant::findOrFail($sourcingVariant->product_variant_id);
                        $oldQuantity = $productVariant->quantity;
                        $newQuantity = $oldQuantity + $sourcingVariant->quantity;
                        
                        $productVariant->update([
                            'quantity' => $newQuantity
                        ]);

                        $productVariants[] = $productVariant;
                        
                        // Log the restock operation specifically
                        Log::info('Product variant restocked', [
                            'variant_id' => $productVariant->id,
                            'variant_name' => $productVariant->variant_name,
                            'old_quantity' => $oldQuantity,
                            'added_quantity' => $sourcingVariant->quantity,
                            'new_quantity' => $newQuantity,
                            'sourcing_id' => $sourcing->id
                        ]);
                    }
                }
            } else {
                // Product doesn't have variants - update the product quantity directly
                $newProductQuantity = $oldProductQuantity + $validated['quantity'];
                $productData['quantity'] = $newProductQuantity;
                
                // Log the product restock operation
                Log::info('Product restocked directly', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'old_quantity' => $oldProductQuantity,
                    'added_quantity' => $validated['quantity'],
                    'new_quantity' => $newProductQuantity,
                    'sourcing_id' => $sourcing->id
                ]);
            }

            // Update product using repository (will trigger ProductHistoryObserver)
            $product = $this->productRepository->update($sourcing->product_id, $productData, [], true);

            // Update sourcing record (will trigger SourcingHistoryObserver)
            $sourcing->update([
                'status' => 'completed',
                'selling_price' => $validated['selling_price'], 
                'buying_price' => $validated['buying_price'],
                'product_url' => $validated['product_url'] ?? $sourcing->product_url,
                'quantity' => $validated['quantity'],
                'weight' => $validated['weight'],
                'destination_country' => $validated['destination_country'],
                'shipping_method' => $validated['shipping_method'],
                'shipping_cost' => $validated['shipping_cost'],
                'cost_per_unit' => $validated['cost_per_unit'],
                'additional_fees' => $validated['additional_fees'],
            ]);

            DB::commit();

            Log::info('Restock sourcing arrived processed successfully', [
                'sourcing_id' => $sourcing->id,
                'product_id' => $product->id
            ]);

            return response()->json([
                'message' => 'Restock sourcing processed successfully',
                'code' => 'SUCCESS',
                'data' => [
                    'sourcing' => $sourcing,
                    'product' => $product
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
