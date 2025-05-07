<?php

namespace MarketingPlanBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum NodeStageStatus: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case PENDING = 'pending';
    case RUNNING = 'running';
    case FINISHED = 'finished';
    case DROPPED = 'dropped';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => '等待执行',
            self::RUNNING => '执行中',
            self::FINISHED => '已完成',
            self::DROPPED => '已流失',
        };
    }
}
