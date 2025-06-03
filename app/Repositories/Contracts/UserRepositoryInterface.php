<?php

namespace App\Repositories\Contracts;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function deliveries();
    public function agents();
    public function findByRole($role, $get);
}
