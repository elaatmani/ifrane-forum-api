<?php

use App\Enums\OrderConfirmationEnum;
use App\Enums\OrderDeliveryEnum;
use App\Enums\OrderCancellationReasonEnum;

return [

    'confirmation' => [
        'default' => OrderConfirmationEnum::NEW,
    ],

    'delivery' => [
        'default' => OrderDeliveryEnum::DISPATCHED,

        'options' => [
            '0' => OrderDeliveryEnum::NOT_SELECTED->value,
            '1' => OrderDeliveryEnum::RESERVED_BEFORE_DELIVERY->value,
            '2' => OrderDeliveryEnum::SENT_FOR_DELIVERY->value,
            '3' => OrderDeliveryEnum::IN_COMPANY->value,
            '4' => OrderDeliveryEnum::WITH_COURIER->value,
            '5' => OrderDeliveryEnum::RETURNED_TO_COMPANY->value,
            '6' => OrderDeliveryEnum::RETURN_RECEIVED->value,
            '7' => OrderDeliveryEnum::DELIVERED->value,
            '8' => OrderDeliveryEnum::SETTLED->value,
            '9' => OrderDeliveryEnum::DELETED->value,
            '10' => OrderDeliveryEnum::RETURN_RESENT->value,
            '11' => OrderDeliveryEnum::RETURN_LOST->value,
            '12' => OrderDeliveryEnum::RETURN_DESTROYED->value,
            '13' => OrderDeliveryEnum::POSTPONED_WITH_COURIER->value,
            '14' => OrderDeliveryEnum::TO_COURIER->value,
            '15' => OrderDeliveryEnum::RETURN_WITH_COURIER->value,
            '16' => OrderDeliveryEnum::ON_WAY_TO_BRANCH->value,
            '17' => OrderDeliveryEnum::IN_BRANCH->value,
            '18' => OrderDeliveryEnum::RETURN_TO_BRANCH->value,
            '19' => OrderDeliveryEnum::RETURN_IN_BRANCH->value,
            '20' => OrderDeliveryEnum::DISPATCHED->value,
        ]
    ],

    'cancellation_reasons' => [
        'options' => [
            OrderCancellationReasonEnum::PRICE_ISSUE->value => 'Price issue',
            OrderCancellationReasonEnum::CHANGED_MIND->value => 'Changed mind',
            OrderCancellationReasonEnum::FOUND_ELSEWHERE->value => 'Found elsewhere',
            OrderCancellationReasonEnum::PRODUCT_UNAVAILABLE->value => 'Product unavailable',
            OrderCancellationReasonEnum::DELIVERY_TIME->value => 'Delivery time too long',
            OrderCancellationReasonEnum::SHIPPING_COST->value => 'Shipping cost too high',
            OrderCancellationReasonEnum::WRONG_ORDER->value => 'Wrong order',
            OrderCancellationReasonEnum::OTHER->value => 'Other',
        ]
    ]
];