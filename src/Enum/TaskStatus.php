<?php

namespace MarketingPlanBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum TaskStatus: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case DRAFT = 'draft';
    case RUNNING = 'running';
    case PAUSED = 'paused';
    case FINISHED = 'finished';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => '草稿',
            self::RUNNING => '运行中',
            self::PAUSED => '已暂停',
            self::FINISHED => '已结束',
        };
    }
}
