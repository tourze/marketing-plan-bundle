<?php

namespace MarketingPlanBundle\Tests\Enum;

use MarketingPlanBundle\Enum\ProgressStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(ProgressStatus::class)]
final class ProgressStatusTest extends AbstractEnumTestCase
{
    public function testCases(): void
    {
        $expectedCases = [
            'PENDING',
            'RUNNING',
            'FINISHED',
            'DROPPED',
        ];

        $actualCases = array_map(
            static fn (ProgressStatus $case) => $case->name,
            ProgressStatus::cases()
        );

        $this->assertSame($expectedCases, $actualCases);
        $this->assertCount(4, ProgressStatus::cases());
    }

    public function testValues(): void
    {
        $this->assertSame('pending', ProgressStatus::PENDING->value);
        $this->assertSame('running', ProgressStatus::RUNNING->value);
        $this->assertSame('finished', ProgressStatus::FINISHED->value);
        $this->assertSame('dropped', ProgressStatus::DROPPED->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('等待进入下一个节点', ProgressStatus::PENDING->getLabel());
        $this->assertSame('当前节点正在执行', ProgressStatus::RUNNING->getLabel());
        $this->assertSame('流程已完成', ProgressStatus::FINISHED->getLabel());
        $this->assertSame('流程中途退出', ProgressStatus::DROPPED->getLabel());
    }

    public function testToArray(): void
    {
        $result = ProgressStatus::PENDING->toArray();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertSame('pending', $result['value']);
        $this->assertSame('等待进入下一个节点', $result['label']);

        $result = ProgressStatus::RUNNING->toArray();
        $this->assertSame('running', $result['value']);
        $this->assertSame('当前节点正在执行', $result['label']);

        $result = ProgressStatus::FINISHED->toArray();
        $this->assertSame('finished', $result['value']);
        $this->assertSame('流程已完成', $result['label']);

        $result = ProgressStatus::DROPPED->toArray();
        $this->assertSame('dropped', $result['value']);
        $this->assertSame('流程中途退出', $result['label']);
    }
}
