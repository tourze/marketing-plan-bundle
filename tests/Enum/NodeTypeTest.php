<?php

namespace MarketingPlanBundle\Tests\Enum;

use MarketingPlanBundle\Enum\NodeType;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(NodeType::class)]
final class NodeTypeTest extends AbstractEnumTestCase
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
            static fn (NodeType $case) => $case->name,
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

    public function testToArray(): void
    {
        $result = NodeType::START->toArray();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertSame('start', $result['value']);
        $this->assertSame('流程开始', $result['label']);

        $result = NodeType::DELAY->toArray();
        $this->assertSame('delay', $result['value']);
        $this->assertSame('延时等待', $result['label']);

        $result = NodeType::CONDITION->toArray();
        $this->assertSame('condition', $result['value']);
        $this->assertSame('条件判断', $result['label']);

        $result = NodeType::RESOURCE->toArray();
        $this->assertSame('resource', $result['value']);
        $this->assertSame('资源派发', $result['label']);

        $result = NodeType::END->toArray();
        $this->assertSame('end', $result['value']);
        $this->assertSame('流程结束', $result['label']);
    }
}
