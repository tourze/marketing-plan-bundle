<?php

namespace MarketingPlanBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum RewardType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case SEND_COUPON = 'send-coupon';
    case GIVE_CREDIT = 'give-credit';

    public function getLabel(): string
    {
        return match ($this) {
            self::SEND_COUPON => '发送优惠券',
            self::GIVE_CREDIT => '赠送积分',
        };
    }
}
