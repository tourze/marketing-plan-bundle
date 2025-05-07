<?php

namespace MarketingPlanBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum DropReason: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case TIMEOUT = 'timeout';
    case CONDITION_NOT_MET = 'condition_not_met';

    public function getLabel(): string
    {
        return match ($this) {
            self::TIMEOUT => '超时未响应',
            self::CONDITION_NOT_MET => '不满足条件',
        };
    }
}
