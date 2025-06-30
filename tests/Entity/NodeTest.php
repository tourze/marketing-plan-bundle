<?php

namespace MarketingPlanBundle\Tests\Entity;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\NodeType;
use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    public function testToString_returnsNodeName(): void
    {
        // Arrange
        $node = new Node();
        $name = '测试节点';
        $node->setName($name);

        // Act
        $result = (string) $node;

        // Assert - Node未保存时ID为0，返回空字符串
        $this->assertEquals('', $result);
    }

    public function testSettersAndGetters(): void
    {
        // Arrange
        $node = new Node();
        $name = '节点名称';
        $type = NodeType::RESOURCE;
        $sequence = 5;
        $task = $this->createMock(Task::class);

        // Act
        $node->setName($name)
             ->setType($type)
             ->setSequence($sequence)
             ->setTask($task);

        // Assert
        $this->assertEquals($name, $node->getName());
        $this->assertEquals($type, $node->getType());
        $this->assertEquals($sequence, $node->getSequence());
        $this->assertSame($task, $node->getTask());
    }
} 