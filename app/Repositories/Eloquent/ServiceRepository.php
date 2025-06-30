<?php

namespace App\Repositories\Eloquent;

use App\Models\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;

class ServiceRepository extends BaseRepository implements ServiceRepositoryInterface
{
    public function __construct(Service $model)
    {
        parent::__construct($model);
    }

    public function create(array $data)
    {
        $service = $this->model->create($data);

        if (isset($data['categories'])) {
            $service->categories()->attach($data['categories']);
        }

        return $service;
    }

    public function update($id, array $data)
    {
        $service = $this->find($id);
        
        if (!$service) {
            return null;
        }

        // Extract categories for separate handling
        $categories = isset($data['categories']) ? $data['categories'] : null;
        unset($data['categories']);

        // Update the service with remaining data
        $service->update($data);

        // Handle categories relationship
        if ($categories !== null) {
            $service->categories()->sync($categories);
        }

        return $service->fresh();
    }


}
