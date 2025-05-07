<?php

namespace MarketingPlanBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum ConditionOperator: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case EQUAL = 'eq';
    case NOT_EQUAL = 'neq';
    case GREATER_THAN = 'gt';
    case GREATER_THAN_OR_EQUAL = 'gte';
    case LESS_THAN = 'lt';
    case LESS_THAN_OR_EQUAL = 'lte';
    case CONTAINS = 'contains';
    case NOT_CONTAINS = 'not_contains';
    case IN = 'in';
    case NOT_IN = 'not_in';

    public function getLabel(): string
    {
        return match ($this) {
            self::EQUAL => '等于',
            self::NOT_EQUAL => '不等于',
            self::GREATER_THAN => '大于',
            self::GREATER_THAN_OR_EQUAL => '大于等于',
            self::LESS_THAN => '小于',
            self::LESS_THAN_OR_EQUAL => '小于等于',
            self::CONTAINS => '包含',
            self::NOT_CONTAINS => '不包含',
            self::IN => '在范围内',
            self::NOT_IN => '不在范围内',
        };
    }
}
