<?php

namespace App\Repositories\Eloquent;

use App\Models\Session;
use App\Repositories\Contracts\SessionRepositoryInterface;

class SessionRepository extends BaseRepository implements SessionRepositoryInterface
{
    public function __construct(Session $model)
    {
        parent::__construct($model);
    }

    public function upcoming($perPage)
    {
        return $this->model->where('start_date', '>=', now())->paginate($perPage);
    }

    public function past($perPage)
    {
        return $this->model->where('start_date', '<', now())->paginate($perPage);
    }



}
