<?php

namespace App\Repositories\Eloquent;

use App\Models\Option;
use App\Repositories\Contracts\OptionRepositoryInterface;

class OptionRepository extends BaseRepository implements OptionRepositoryInterface
{
    public function __construct(Option $model)
    {
        parent::__construct($model);
    }


    public function getOptionByName(string $name) {
        return $this->model->where('name', $name)->first();
    }

    public function setOption(string $name, string $value) {
        $option = $this->getOptionByName($name);
        if (!$option) {
            $option = $this->model->create([
                'name' => $name,
                'value' => $value,
            ]);
        } else {
            $option->update([
                'value' => $value,
            ]);
        }
    }

}
