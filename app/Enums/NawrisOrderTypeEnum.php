<?php

namespace App\Enums;

enum NawrisOrderTypeEnum: int
{
    // استلام طلب مع التحصيل
    case NORMAL = 0; // لا
    case RETURN = 1; // مرتجع جزئي
    case CHANGE = 2; // مرتجع استبدال
    case REFUND = 3; // مرتجع استرجاع 
}