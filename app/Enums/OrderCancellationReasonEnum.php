<?php

namespace App\Enums;

enum OrderCancellationReasonEnum: string
{
    case PRICE_ISSUE = 'price-issue';
    case CHANGED_MIND = 'changed-mind';
    case FOUND_ELSEWHERE = 'found-elsewhere';
    case PRODUCT_UNAVAILABLE = 'product-unavailable';
    case DELIVERY_TIME = 'delivery-time';
    case SHIPPING_COST = 'shipping-cost';
    case WRONG_ORDER = 'wrong-order';
    case OTHER = 'other';
} 