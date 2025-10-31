<?php

namespace MarketingPlanBundle\Tests\Enum;

use MarketingPlanBundle\Enum\NodeStageStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(NodeStageStatus::class)]
final class NodeStageStatusTest extends AbstractEnumTestCase
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
            static fn (NodeStageStatus $case) => $case->name,
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

    public function testToArray(): void
    {
        $result = NodeStageStatus::PENDING->toArray();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertSame('pending', $result['value']);
        $this->assertSame('等待执行', $result['label']);

        $result = NodeStageStatus::RUNNING->toArray();
        $this->assertSame('running', $result['value']);
        $this->assertSame('执行中', $result['label']);

        $result = NodeStageStatus::FINISHED->toArray();
        $this->assertSame('finished', $result['value']);
        $this->assertSame('已完成', $result['label']);

        $result = NodeStageStatus::DROPPED->toArray();
        $this->assertSame('dropped', $result['value']);
        $this->assertSame('已流失', $result['label']);
    }
}
