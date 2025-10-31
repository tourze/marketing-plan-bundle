<?php

namespace MarketingPlanBundle\Tests\Enum;

use MarketingPlanBundle\Enum\DelayType;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(DelayType::class)]
final class DelayTypeTest extends AbstractEnumTestCase
{
    public function testCases(): void
    {
        $cases = DelayType::cases();
        $this->assertCount(4, $cases);

        $expectedCases = [
            DelayType::MINUTES,
            DelayType::HOURS,
            DelayType::DAYS,
            DelayType::SPECIFIC_TIME,
        ];

        foreach ($expectedCases as $expectedCase) {
            $this->assertContains($expectedCase, $cases);
        }
    }

    public function testValues(): void
    {
        $this->assertSame('minutes', DelayType::MINUTES->value);
        $this->assertSame('hours', DelayType::HOURS->value);
        $this->assertSame('days', DelayType::DAYS->value);
        $this->assertSame('specific_time', DelayType::SPECIFIC_TIME->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('分钟', DelayType::MINUTES->getLabel());
        $this->assertSame('小时', DelayType::HOURS->getLabel());
        $this->assertSame('天', DelayType::DAYS->getLabel());
        $this->assertSame('具体时间', DelayType::SPECIFIC_TIME->getLabel());
    }

    public function testToArray(): void
    {
        $result = DelayType::MINUTES->toArray();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertSame('minutes', $result['value']);
        $this->assertSame('分钟', $result['label']);

        $result = DelayType::HOURS->toArray();
        $this->assertSame('hours', $result['value']);
        $this->assertSame('小时', $result['label']);

        $result = DelayType::DAYS->toArray();
        $this->assertSame('days', $result['value']);
        $this->assertSame('天', $result['label']);

        $result = DelayType::SPECIFIC_TIME->toArray();
        $this->assertSame('specific_time', $result['value']);
        $this->assertSame('具体时间', $result['label']);
    }
}
