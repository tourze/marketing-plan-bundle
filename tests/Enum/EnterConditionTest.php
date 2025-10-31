<?php

namespace MarketingPlanBundle\Tests\Enum;

use MarketingPlanBundle\Enum\EnterCondition;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(EnterCondition::class)]
final class EnterConditionTest extends AbstractEnumTestCase
{
    public function testCases(): void
    {
        $expectedCases = [
            'VISIT_URL',
            'VISIT_PATH',
            'MOBILE_REGISTER',
            'GATHER_COUPON',
            'CONSUME_COUPON',
        ];

        $actualCases = array_map(
            static fn (EnterCondition $case) => $case->name,
            EnterCondition::cases()
        );

        $this->assertSame($expectedCases, $actualCases);
        $this->assertCount(5, EnterCondition::cases());
    }

    public function testValues(): void
    {
        $this->assertSame('visit-url', EnterCondition::VISIT_URL->value);
        $this->assertSame('visit-wechat-mini-program-path', EnterCondition::VISIT_PATH->value);
        $this->assertSame('mobile-register', EnterCondition::MOBILE_REGISTER->value);
        $this->assertSame('gather-coupon', EnterCondition::GATHER_COUPON->value);
        $this->assertSame('consume-coupon', EnterCondition::CONSUME_COUPON->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('打开网页URL', EnterCondition::VISIT_URL->getLabel());
        $this->assertSame('打开小程序路径', EnterCondition::VISIT_PATH->getLabel());
        $this->assertSame('手机号码注册', EnterCondition::MOBILE_REGISTER->getLabel());
        $this->assertSame('领取优惠券', EnterCondition::GATHER_COUPON->getLabel());
        $this->assertSame('使用优惠券', EnterCondition::CONSUME_COUPON->getLabel());
    }

    public function testToArray(): void
    {
        $result = EnterCondition::VISIT_URL->toArray();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertSame('visit-url', $result['value']);
        $this->assertSame('打开网页URL', $result['label']);

        $result = EnterCondition::VISIT_PATH->toArray();
        $this->assertSame('visit-wechat-mini-program-path', $result['value']);
        $this->assertSame('打开小程序路径', $result['label']);

        $result = EnterCondition::MOBILE_REGISTER->toArray();
        $this->assertSame('mobile-register', $result['value']);
        $this->assertSame('手机号码注册', $result['label']);
    }
}
