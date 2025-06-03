<?php

namespace App\Rules\Order;

use Closure;
use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductOrVariant implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get the current item data
        $itemData = request()->input($attribute);
        
        // Check for product_id and product_variation_id
        $productIdExists = isset($itemData['product_id']);
        $productVariationIdExists = isset($itemData['product_variant_id']);

        if (!$productIdExists && !$productVariationIdExists) {
            $fail('product-not-valid');
        }
        
        $product = Product::where('id', $itemData['product_id'])->first();

        // $fail('test-' . json_encode($itemData));
        
        if(!$product) {
            $fail('product-not-valid');
        }

        if($product && $product->variants->isNotEmpty() && !$productVariationIdExists) {
            $fail('product-variant-required');
        }

    }
}
