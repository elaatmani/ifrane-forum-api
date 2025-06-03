<?php

namespace App\Http\Controllers\Sourcing;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Sourcing\SourcingListResource;
use App\Repositories\Contracts\SourcingRepositoryInterface;
use App\Models\Sourcing;
use App\Models\SourcingVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SourcingUpdateController extends Controller
{
    protected $repository;

    public function __construct(SourcingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        // Fields that can be updated
        $can_be_updated = [
            'product_name',
            'quantity',
            'destination_country',
            'shipping_method',
            'product_url',
            'status',
            'cost_per_unit',
            'shipping_cost',
            'additional_fees',
            'note',
            'buying_price',
            'selling_price',
            'weight',
            'variants'
        ];

        try {
            // Start transaction
            DB::beginTransaction();
            
            // Find the sourcing record to validate it exists
            $sourcing = Sourcing::with('variants')->findOrFail($id);
            
            // Filter only the fields that can be updated
            $fields = array_intersect_key($request->all(), array_flip($can_be_updated));
            
            // If specific fields were requested, limit to those
            if ($request->has('fields')) {
                $fields = Arr::only($fields, $request->fields);
            }

            // Handle variants update if provided
            if (isset($fields['variants'])) {
                $this->updateSourcingVariants($sourcing, $fields['variants']);
                unset($fields['variants']); // Remove variants from fields to be updated directly
            }

            // Update the sourcing with the filtered fields
            $updatedSourcing = $this->repository->update($id, $fields);
            
            // Load the variants relationship
            $updatedSourcing->load('variants');
            
            DB::commit();
            
            return response()->json([
                'sourcing' => new SourcingListResource($updatedSourcing),
                'code' => 'SUCCESS',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'code' => 'ERROR',
                'error_message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ], 500);
        }
    }
    
    /**
     * Update sourcing variants
     * 
     * @param Sourcing $sourcing
     * @param array $variants
     * @return void
     */
    protected function updateSourcingVariants(Sourcing $sourcing, array $variants)
    {
        Log::info('Starting sourcing variant update', [
            'sourcing_id' => $sourcing->id,
            'variant_count' => count($variants),
            'variants' => $variants
        ]);
        
        // Separate variants into existing (to update) and new (to create)
        $existingVariants = [];
        $newVariants = [];
        
        foreach ($variants as $variant) {
            if (isset($variant['new'])) {
                $newVariants[] = $variant;
            } else {
                $existingVariants[] = $variant;
            }
        }
        
        Log::info('Variant separation results', [
            'sourcing_id' => $sourcing->id,
            'existing_count' => count($existingVariants),
            'new_count' => count($newVariants)
        ]);
        
        // STEP 1: Update existing variants
        $validVariantIds = [];
        
        foreach ($existingVariants as $index => $variant) {
            Log::info('Updating existing variant', [
                'sourcing_id' => $sourcing->id,
                'variant_index' => $index,
                'variant_id' => $variant['id'] ?? null,
                'variant_data' => $variant
            ]);
            
            $updated = SourcingVariant::where('id', $variant['id'])
                ->where('sourcing_id', $sourcing->id)
                ->update([
                    'variant_name' => $variant['variant_name'] ?? null,
                    'quantity' => $variant['quantity'] ?? null,
                    'product_variant_id' => $variant['product_variant_id'] ?? null,
                ]);
            
            Log::info('Variant update result', [
                'sourcing_id' => $sourcing->id,
                'variant_id' => $variant['id'] ?? null,
                'rows_affected' => $updated
            ]);
            
            $validVariantIds[] = $variant['id'];
        }
        
        // STEP 2: Delete variants not included in the update
        if (!empty($validVariantIds)) {
            Log::info('Preparing to delete variants not in update', [
                'sourcing_id' => $sourcing->id,
                'valid_variant_ids' => $validVariantIds
            ]);
            
            $toDelete = $sourcing->variants()
                ->whereNotIn('id', $validVariantIds)
                ->pluck('id')
                ->toArray();
                
            Log::info('Variants to be deleted', [
                'sourcing_id' => $sourcing->id,
                'variant_ids_to_delete' => $toDelete,
                'count' => count($toDelete)
            ]);
            
            if (!empty($toDelete)) {
                $deleted = $sourcing->variants()
                    ->whereNotIn('id', $validVariantIds)
                    ->delete();
                    
                Log::info('Variants deleted', [
                    'sourcing_id' => $sourcing->id,
                    'rows_affected' => $deleted
                ]);
            } else {
                // If no existing variants were specified, delete all variants
                if (count($sourcing->variants) > 0 && empty($existingVariants)) {
                    Log::info('Deleting all existing variants as none were included in update', [
                        'sourcing_id' => $sourcing->id
                    ]);
                    
                    $deleted = $sourcing->variants()->delete();
                    
                    Log::info('All variants deleted', [
                        'sourcing_id' => $sourcing->id,
                        'rows_affected' => $deleted
                    ]);
                } else {
                    Log::info('No variants to delete - no valid IDs found', [
                        'sourcing_id' => $sourcing->id
                    ]);
                }
            }
        } else {
            // If no existing variants were specified, delete all variants
            if (count($sourcing->variants) > 0 && empty($existingVariants)) {
                Log::info('Deleting all existing variants as none were included in update', [
                    'sourcing_id' => $sourcing->id
                ]);
                
                $deleted = $sourcing->variants()->delete();
                
                Log::info('All variants deleted', [
                    'sourcing_id' => $sourcing->id,
                    'rows_affected' => $deleted
                ]);
            } else {
                Log::info('No variants to delete - no valid IDs found', [
                    'sourcing_id' => $sourcing->id
                ]);
            }
        }
        
        // STEP 3: Create new variants
        foreach ($newVariants as $index => $variant) {
            Log::info('Creating new variant', [
                'sourcing_id' => $sourcing->id,
                'variant_index' => $index,
                'variant_data' => $variant
            ]);
            
            $newVariant = SourcingVariant::create([
                'variant_name' => $variant['variant_name'] ?? null,
                'quantity' => $variant['quantity'] ?? null,
                'product_variant_id' => $variant['product_variant_id'] ?? null,
                'sourcing_id' => $sourcing->id
            ]);
            
            Log::info('New variant created', [
                'sourcing_id' => $sourcing->id,
                'variant_id' => $newVariant->id,
                'variant_name' => $newVariant->variant_name
            ]);
        }
        
        Log::info('Sourcing variant update completed', [
            'sourcing_id' => $sourcing->id
        ]);
    }
}
