<?php

namespace MarketingPlanBundle\Tests\Unit\Enum;

use MarketingPlanBundle\Enum\ProgressStatus;
use PHPUnit\Framework\TestCase;

class ProgressStatusTest extends TestCase
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
            static fn(ProgressStatus $case) => $case->name,
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
}