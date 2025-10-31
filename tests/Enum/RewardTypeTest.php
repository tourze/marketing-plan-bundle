<?php

namespace MarketingPlanBundle\Tests\Enum;

use MarketingPlanBundle\Enum\RewardType;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(RewardType::class)]
final class RewardTypeTest extends AbstractEnumTestCase
{
    public function testCases(): void
    {
        $expectedCases = [
            'SEND_COUPON',
            'GIVE_CREDIT',
        ];

        $actualCases = array_map(
            static fn (RewardType $case) => $case->name,
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

    public function testToArray(): void
    {
        $result = RewardType::SEND_COUPON->toArray();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertSame('send-coupon', $result['value']);
        $this->assertSame('发送优惠券', $result['label']);

        $result = RewardType::GIVE_CREDIT->toArray();
        $this->assertSame('give-credit', $result['value']);
        $this->assertSame('赠送积分', $result['label']);
    }
}
