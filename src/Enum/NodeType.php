<?php

namespace MarketingPlanBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum NodeType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case START = 'start';
    case DELAY = 'delay';
    case CONDITION = 'condition';
    case RESOURCE = 'resource';
    case END = 'end';

    public function getLabel(): string
    {
        return match ($this) {
            self::START => '流程开始',
            self::DELAY => '延时等待',
            self::CONDITION => '条件判断',
            self::RESOURCE => '资源派发',
            self::END => '流程结束',
        };
    }
}
