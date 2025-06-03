<?php

namespace App\Repositories\Eloquent;

use App\Models\Sourcing;
use App\Models\SourcingVariant;
use App\Repositories\Contracts\SourcingRepositoryInterface;

class SourcingRepository extends BaseRepository implements SourcingRepositoryInterface
{
    public function __construct(Sourcing $model)
    {
        parent::__construct($model);
    }

    public function create($data) {
        $data['created_by'] = auth()->id(); // Set the created_by field to the authenticated user's ID

        if(data_get($data, 'sourcing_type') == 'new_product') {
            return $this->createNewProductSourcing($data);
        } elseif(data_get($data, 'sourcing_type') == 'restock') {
            return $this->createRestockSourcing($data);
        }
        
        return null;
    }

    public function createNewProductSourcing($data)
    {
        $sourcing = parent::create([
            'created_by' => auth()->id(),
            'product_name' => data_get($data, 'product_name'),
            'product_url' => data_get($data, 'product_url'),
            'quantity' => data_get($data, 'quantity'),
            'destination_country' => data_get($data, 'destination_country'),
            'note' => data_get($data, 'note'),
            'shipping_method' => data_get($data, 'shipping_method'),
            'status' => data_get($data, 'status', 'pending'),
            'cost_per_unit' => data_get($data, 'cost_per_unit'),
            'shipping_cost' => data_get($data, 'shipping_cost'),
            'additional_fees' => data_get($data, 'additional_fees'),
            'buying_price' => data_get($data, 'buying_price'),
            'selling_price' => data_get($data, 'selling_price'),
            'weight' => data_get($data, 'weight'),
            'product_id' => null,
            'sourcing_type' => 'new_product'
        ]);

        $sourcing_variants = data_get($data, 'variants', []);

        foreach($sourcing_variants as $sourcing_variant) {
            SourcingVariant::create([
                'variant_name' => $sourcing_variant['variant_name'],
                'quantity' => $sourcing_variant['quantity'],
                'sourcing_id' => $sourcing->id
            ]);
        }
        
        $sourcing->load('variants');

        return $sourcing;
    }

    public function createRestockSourcing($data)
    {
        $sourcing = parent::create([
            'created_by' => auth()->id(),
            'product_name' => data_get($data, 'product_name'),
            'product_url' => data_get($data, 'product_url', null),
            'quantity' => data_get($data, 'quantity'),
            'destination_country' => data_get($data, 'destination_country'),
            'note' => data_get($data, 'note', null),
            'shipping_method' => data_get($data, 'shipping_method'),
            'status' => data_get($data, 'status', 'pending'),
            'cost_per_unit' => data_get($data, 'cost_per_unit'),
            'shipping_cost' => data_get($data, 'shipping_cost'),
            'additional_fees' => data_get($data, 'additional_fees'),
            'buying_price' => data_get($data, 'buying_price'),
            'selling_price' => data_get($data, 'selling_price'),
            'weight' => data_get($data, 'weight'),
            'product_id' => data_get($data, 'product_id'),
            'sourcing_type' => 'restock'
        ]);

        $selected_variants = data_get($data, 'selected_variants', []);

        foreach($selected_variants as $selected_variant) {
            SourcingVariant::create([
                'variant_name' => $selected_variant['variant_name'],
                'quantity' => $selected_variant['quantity'],
                'product_variant_id' => $selected_variant['variant_id'],
                'sourcing_id' => $sourcing->id
            ]);
        }
        
        $sourcing->load('variants');
        
        return $sourcing;
    }
}
