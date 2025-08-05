<?php

namespace App\Repositories\Contracts;

interface SessionRepositoryInterface extends BaseRepositoryInterface
{
    public function upcoming($perPage);
    public function past($perPage);
}
