<?php

namespace MarketingPlanBundle\Tests\Enum;

use MarketingPlanBundle\Enum\LogStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(LogStatus::class)]
final class LogStatusTest extends AbstractEnumTestCase
{
    public function testCases(): void
    {
        $cases = LogStatus::cases();
        $this->assertCount(4, $cases);

        $expectedCases = [
            LogStatus::IN_PROGRESS,
            LogStatus::COMPLETED,
            LogStatus::FAILED,
            LogStatus::CANCELLED,
        ];

        foreach ($expectedCases as $expectedCase) {
            $this->assertContains($expectedCase, $cases);
        }
    }

    public function testValues(): void
    {
        $this->assertSame('in_progress', LogStatus::IN_PROGRESS->value);
        $this->assertSame('completed', LogStatus::COMPLETED->value);
        $this->assertSame('failed', LogStatus::FAILED->value);
        $this->assertSame('cancelled', LogStatus::CANCELLED->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('进行中', LogStatus::IN_PROGRESS->getLabel());
        $this->assertSame('已完成', LogStatus::COMPLETED->getLabel());
        $this->assertSame('失败', LogStatus::FAILED->getLabel());
        $this->assertSame('已取消', LogStatus::CANCELLED->getLabel());
    }

    public function testToArray(): void
    {
        $result = LogStatus::IN_PROGRESS->toArray();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertSame('in_progress', $result['value']);
        $this->assertSame('进行中', $result['label']);

        $result = LogStatus::COMPLETED->toArray();
        $this->assertSame('completed', $result['value']);
        $this->assertSame('已完成', $result['label']);

        $result = LogStatus::FAILED->toArray();
        $this->assertSame('failed', $result['value']);
        $this->assertSame('失败', $result['label']);

        $result = LogStatus::CANCELLED->toArray();
        $this->assertSame('cancelled', $result['value']);
        $this->assertSame('已取消', $result['label']);
    }
}
