<?php

namespace MarketingPlanBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum EnterCondition: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case VISIT_URL = 'visit-url';
    case VISIT_PATH = 'visit-wechat-mini-program-path';
    case MOBILE_REGISTER = 'mobile-register';
    case GATHER_COUPON = 'gather-coupon';
    case CONSUME_COUPON = 'consume-coupon';

    public function getLabel(): string
    {
        return match ($this) {
            self::VISIT_URL => '打开网页URL',
            self::VISIT_PATH => '打开小程序路径',
            self::MOBILE_REGISTER => '手机号码注册',
            self::GATHER_COUPON => '领取优惠券',
            self::CONSUME_COUPON => '使用优惠券',
        };
    }
}
