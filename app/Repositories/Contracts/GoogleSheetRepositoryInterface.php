<?php

namespace App\Repositories\Contracts;

interface GoogleSheetRepositoryInterface extends BaseRepositoryInterface
{
    public function insertMany(array $orders);
}
