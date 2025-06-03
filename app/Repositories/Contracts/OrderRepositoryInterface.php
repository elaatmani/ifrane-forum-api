<?php

namespace App\Repositories\Contracts;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function insertMany(array $orders);
    public function getOrderStatusForAgent($agentId);
    public function getProductsConfirmationByAgent($agentId);
}
