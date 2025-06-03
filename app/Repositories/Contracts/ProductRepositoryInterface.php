<?php

namespace App\Repositories\Contracts;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function getOrderCountForProduct($productId);
    public function getOrderConfirmationForProduct($productId);
    public function getOrderDeliveryForProduct($productId);
    public function getTotalQuantityForProduct($productId);
    public function getAvailableQuantityForProduct($productId);
    public function getShippedQuantityForProduct($productId);
    public function getDeliveredQuantityForProduct($productId);
    public function getTotalDeliveredOrdersForProduct($productId);
    public function getTotalConfirmedOrdersForProduct($productId);
    public function getAgentsForProduct($productId);
    
    public function getAgentsForProductByRange($productId, $params = []);
    public function getMarketersForProduct($productId);
    public function getMarketersForProductByRange($productId, $params = []);
    public function getMarketersWithAdsForProduct($productId);
    public function getStatusForProduct($productId);
    public function getAdSpendForProduct($productId);

    public function getOffersForProduct($ids);
    public function getCrossProductsForProduct($ids);
    public function topProducts($params);
    public function productAnalytics($params);

    public function getTotalDuplicatedOrdersForProduct($productId);
}
