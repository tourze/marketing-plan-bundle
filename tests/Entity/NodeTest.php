<?php

namespace MarketingPlanBundle\Tests\Entity;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\NodeType;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;

/**
 * @internal
 */
#[CoversClass(Node::class)]
final class NodeTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setResource($resourceConfig);

        return $node;
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'name' => ['name', '测试节点'];
        yield 'type' => ['type', NodeType::RESOURCE];
        yield 'sequence' => ['sequence', 5];
        yield 'task' => ['task', new Task()];
    }

    public function testToStringReturnsEmptyStringWhenIdIsZero(): void
    {
        // Arrange
        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setResource($resourceConfig);
        $name = '测试节点';
        $node->setName($name);

        // Act
        $result = (string) $node;

        // Assert - Node未保存时ID为0，返回空字符串
        $this->assertEquals('', $result);
    }
}
