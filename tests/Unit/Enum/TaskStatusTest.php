<?php

namespace MarketingPlanBundle\Tests\Unit\Enum;

use MarketingPlanBundle\Enum\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskStatusTest extends TestCase
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
            static fn(TaskStatus $case) => $case->name,
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
}