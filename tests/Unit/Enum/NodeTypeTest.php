<?php

namespace MarketingPlanBundle\Tests\Unit\Enum;

use MarketingPlanBundle\Enum\NodeType;
use PHPUnit\Framework\TestCase;

class NodeTypeTest extends TestCase
{
    public function testCases(): void
    {
        $expectedCases = [
            'START',
            'DELAY',
            'CONDITION',
            'RESOURCE',
            'END',
        ];

        $actualCases = array_map(
            static fn(NodeType $case) => $case->name,
            NodeType::cases()
        );

        $this->assertSame($expectedCases, $actualCases);
        $this->assertCount(5, NodeType::cases());
    }

    public function testValues(): void
    {
        $this->assertSame('start', NodeType::START->value);
        $this->assertSame('delay', NodeType::DELAY->value);
        $this->assertSame('condition', NodeType::CONDITION->value);
        $this->assertSame('resource', NodeType::RESOURCE->value);
        $this->assertSame('end', NodeType::END->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('流程开始', NodeType::START->getLabel());
        $this->assertSame('延时等待', NodeType::DELAY->getLabel());
        $this->assertSame('条件判断', NodeType::CONDITION->getLabel());
        $this->assertSame('资源派发', NodeType::RESOURCE->getLabel());
        $this->assertSame('流程结束', NodeType::END->getLabel());
    }
}