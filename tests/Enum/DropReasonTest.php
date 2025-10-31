<?php

namespace MarketingPlanBundle\Tests\Enum;

use MarketingPlanBundle\Enum\DropReason;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(DropReason::class)]
final class DropReasonTest extends AbstractEnumTestCase
{
    public function testCases(): void
    {
        $cases = DropReason::cases();
        $this->assertCount(2, $cases);

        $expectedCases = [
            DropReason::TIMEOUT,
            DropReason::CONDITION_NOT_MET,
        ];

        foreach ($expectedCases as $expectedCase) {
            $this->assertContains($expectedCase, $cases);
        }
    }

    public function testValues(): void
    {
        $this->assertSame('timeout', DropReason::TIMEOUT->value);
        $this->assertSame('condition_not_met', DropReason::CONDITION_NOT_MET->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('超时未响应', DropReason::TIMEOUT->getLabel());
        $this->assertSame('不满足条件', DropReason::CONDITION_NOT_MET->getLabel());
    }

    public function testToArray(): void
    {
        $result = DropReason::TIMEOUT->toArray();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertSame('timeout', $result['value']);
        $this->assertSame('超时未响应', $result['label']);

        $result = DropReason::CONDITION_NOT_MET->toArray();
        $this->assertSame('condition_not_met', $result['value']);
        $this->assertSame('不满足条件', $result['label']);
    }
}
