<?php

namespace MarketingPlanBundle\Tests\Unit\Enum;

use MarketingPlanBundle\Enum\NodeStageStatus;
use PHPUnit\Framework\TestCase;

class NodeStageStatusTest extends TestCase
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
            static fn(NodeStageStatus $case) => $case->name,
            NodeStageStatus::cases()
        );

        $this->assertSame($expectedCases, $actualCases);
        $this->assertCount(4, NodeStageStatus::cases());
    }

    public function testValues(): void
    {
        $this->assertSame('pending', NodeStageStatus::PENDING->value);
        $this->assertSame('running', NodeStageStatus::RUNNING->value);
        $this->assertSame('finished', NodeStageStatus::FINISHED->value);
        $this->assertSame('dropped', NodeStageStatus::DROPPED->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('等待执行', NodeStageStatus::PENDING->getLabel());
        $this->assertSame('执行中', NodeStageStatus::RUNNING->getLabel());
        $this->assertSame('已完成', NodeStageStatus::FINISHED->getLabel());
        $this->assertSame('已流失', NodeStageStatus::DROPPED->getLabel());
    }
}