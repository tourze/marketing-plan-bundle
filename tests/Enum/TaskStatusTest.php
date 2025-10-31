<?php

namespace MarketingPlanBundle\Tests\Enum;

use MarketingPlanBundle\Enum\TaskStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(TaskStatus::class)]
final class TaskStatusTest extends AbstractEnumTestCase
{
    public function testCases(): void
    {
        $expectedCases = [
            'DRAFT',
            'RUNNING',
            'PAUSED',
            'FINISHED',
        ];

        $actualCases = array_map(
            static fn (TaskStatus $case) => $case->name,
            TaskStatus::cases()
        );

        $this->assertSame($expectedCases, $actualCases);
        $this->assertCount(4, TaskStatus::cases());
    }

    public function testValues(): void
    {
        $this->assertSame('draft', TaskStatus::DRAFT->value);
        $this->assertSame('running', TaskStatus::RUNNING->value);
        $this->assertSame('paused', TaskStatus::PAUSED->value);
        $this->assertSame('finished', TaskStatus::FINISHED->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('草稿', TaskStatus::DRAFT->getLabel());
        $this->assertSame('运行中', TaskStatus::RUNNING->getLabel());
        $this->assertSame('已暂停', TaskStatus::PAUSED->getLabel());
        $this->assertSame('已结束', TaskStatus::FINISHED->getLabel());
    }

    public function testToArray(): void
    {
        $result = TaskStatus::DRAFT->toArray();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertSame('draft', $result['value']);
        $this->assertSame('草稿', $result['label']);

        $result = TaskStatus::RUNNING->toArray();
        $this->assertSame('running', $result['value']);
        $this->assertSame('运行中', $result['label']);

        $result = TaskStatus::PAUSED->toArray();
        $this->assertSame('paused', $result['value']);
        $this->assertSame('已暂停', $result['label']);

        $result = TaskStatus::FINISHED->toArray();
        $this->assertSame('finished', $result['value']);
        $this->assertSame('已结束', $result['label']);
    }
}
