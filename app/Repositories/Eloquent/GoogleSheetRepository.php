<?php

namespace App\Repositories\Eloquent;

use App\Models\GoogleSheet;
use App\Repositories\Contracts\GoogleSheetRepositoryInterface;

class GoogleSheetRepository extends BaseRepository implements GoogleSheetRepositoryInterface
{
    public function __construct(GoogleSheet $model)
    {
        parent::__construct($model);
    }


    public function create(array $data, array $items = [])
    {
        $order = parent::create($data);

        return $order;
    }

    public function insertMany(array $items)
    {
        $result = [];
        foreach ($items as $i) {
            $result[] = $this->create($i, $i['items']);
        }

        return $result;
    }
}
