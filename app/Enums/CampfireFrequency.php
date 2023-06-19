<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum CampfireFrequency: string
{
    use EnumToArray;

    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case TwiceMonthly = 'twice monthly';
    case Quarterly = 'quarterly';
    case EveryOtherMonth = 'every other month';
}
