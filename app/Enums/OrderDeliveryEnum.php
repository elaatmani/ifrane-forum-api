<?php

namespace App\Enums;

enum OrderDeliveryEnum: string
{
    case NOT_SELECTED = 'not-selected';
    case DISPATCHED = 'dispatched';
    case RESERVED_BEFORE_DELIVERY = 'reserved-before-delivery'; // محفوظة قبل التوصيل
    case SENT_FOR_DELIVERY = 'sent-for-delivery'; // مرسلة للتوصيل
    case IN_COMPANY = 'in-company'; // في الشركة
    case WITH_COURIER = 'with-courier'; // مع المندوب
    case RETURNED_TO_COMPANY = 'returned-to-company'; // مرتجع مع الشركة
    case RETURN_RECEIVED = 'return-received'; // مرتجع تم استلامه
    case DELIVERED = 'delivered'; // تم التسليم
    case SETTLED = 'settled'; // تم التسوية
    case DELETED = 'deleted'; // محذوفة
    case RETURN_RESENT = 'return-resent'; // مرتجعة معاد إرساله
    case RETURN_LOST = 'return-lost'; // مرتجع مفقود
    case RETURN_DESTROYED = 'return-destroyed'; // مرتجع معدوم
    case POSTPONED_WITH_COURIER = 'postponed-with-courier'; // مؤجلة مع المندوب
    case TO_COURIER = 'to-courier'; // إلي المندوب
    case RETURN_WITH_COURIER = 'return-with-courier'; // مرتجع مع المندوب
    case ON_WAY_TO_BRANCH = 'on-way-to-branch'; // بالطريق للفرع
    case IN_BRANCH = 'in-branch'; // في الفرع
    case RETURN_TO_BRANCH = 'return-to-branch'; // راجع الى الفرع
    case RETURN_IN_BRANCH = 'return-in-branch'; // مرتجع في الفرع
}