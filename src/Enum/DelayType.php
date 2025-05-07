<?php

namespace MarketingPlanBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum DelayType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case MINUTES = 'minutes';
    case HOURS = 'hours';
    case DAYS = 'days';
    case SPECIFIC_TIME = 'specific_time';

    public function getLabel(): string
    {
        return match ($this) {
            self::MINUTES => '分钟',
            self::HOURS => '小时',
            self::DAYS => '天',
            self::SPECIFIC_TIME => '具体时间',
        };
    }
}
