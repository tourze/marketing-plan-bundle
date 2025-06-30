<?php

namespace MarketingPlanBundle\Tests\Unit\Enum;

use MarketingPlanBundle\Enum\RewardType;
use PHPUnit\Framework\TestCase;

class RewardTypeTest extends TestCase
{
    public function testCases(): void
    {
        $expectedCases = [
            'SEND_COUPON',
            'GIVE_CREDIT',
        ];

        $actualCases = array_map(
            static fn(RewardType $case) => $case->name,
            RewardType::cases()
        );

        $this->assertSame($expectedCases, $actualCases);
        $this->assertCount(2, RewardType::cases());
    }

    public function testValues(): void
    {
        $this->assertSame('send-coupon', RewardType::SEND_COUPON->value);
        $this->assertSame('give-credit', RewardType::GIVE_CREDIT->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('发送优惠券', RewardType::SEND_COUPON->getLabel());
        $this->assertSame('赠送积分', RewardType::GIVE_CREDIT->getLabel());
    }
}