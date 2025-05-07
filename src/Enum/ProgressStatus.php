<?php

namespace MarketingPlanBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum ProgressStatus: string implements Labelable, Itemable, Selectable
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
            self::PENDING => '等待进入下一个节点',
            self::RUNNING => '当前节点正在执行',
            self::FINISHED => '流程已完成',
            self::DROPPED => '流程中途退出',
        };
    }
}
