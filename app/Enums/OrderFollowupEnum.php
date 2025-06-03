<?php

namespace App\Enums;

enum OrderFollowupEnum: string
{
    case NEW = 'new';
    // case DUBPLICATE = 'duplicate';
    // case DAY_ONE_CALL_ONE = 'day-one-call-one';
    // case DAY_ONE_CALL_TWO = 'day-one-call-two';
    // case DAY_ONE_CALL_THREE = 'day-one-call-three';
    // case DAY_TWO_CALL_ONE = 'day-two-call-one';
    // case DAY_TWO_CALL_TWO = 'day-two-call-two';
    // case DAY_TWO_CALL_THREE = 'day-two-call-three';
    // case DAY_THREE_CALL_ONE = 'day-three-call-one';
    // case DAY_THREE_CALL_TWO = 'day-three-call-two';
    // case DAY_THREE_CALL_THREE = 'day-three-call-three';
    case WRONG_NUMBER = 'wrong-number';
    case NO_ANSWER = 'no-answer';
    case REPORTED = 'reported';
    case CANCELED = 'canceled';
    case RECONFIRMED = 'reconfirmed';
    case CHANGE = 'change';
    case REFUND = 'refund';
}