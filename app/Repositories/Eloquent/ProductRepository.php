<?php

namespace App\Repositories\Eloquent;

use Exception;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        
        try {
            $product = $this->model->create($data);

            $product->categories()->attach($data['category_ids']);
            
            DB::commit();
            return $product;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();
        
        try {
            $product = $this->model->find($id);

            if(!$product) {
                throw new Exception('Product not found');
            }
            
            $product->update($data);

            $product->categories()->sync($data['category_ids']);
            
            DB::commit();
            return $product;
        } catch (Exception $e) {
            DB::rollback();
        }
    }


}
