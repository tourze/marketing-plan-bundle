<?php

namespace MarketingPlanBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeCondition;
use MarketingPlanBundle\Entity\NodeDelay;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\ConditionOperator;
use MarketingPlanBundle\Enum\DelayType;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Service\NodeService;
use PHPUnit\Framework\TestCase;



class NodeServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private NodeService $nodeService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->nodeService = new NodeService($this->entityManager);
    }

    public function testCreate_createNodeWithNextSequence(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        $existingNode = $this->createMock(Node::class);
        $existingNode->method('getSequence')->willReturn(5);
        
        // 使用自定义的TestArrayCollection，它包含max方法
        $nodes = new TestArrayCollection([$existingNode]);
        
        // 使用部分模拟来模拟某些方法
        $task = $this->createPartialMock(Task::class, ['getNodes']);
        $task->method('getNodes')->willReturn($nodes);
        
        $name = 'Test Node';
        $type = NodeType::RESOURCE; // 使用实际存在的枚举值
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function(Node $node) use ($name, $type) {
                return $node->getName() === $name && 
                       $node->getType() === $type && 
                       $node->getSequence() === 6;
            }));
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->nodeService->create($task, $name, $type);

        // Assert
        $this->assertEquals($name, $result->getName());
        $this->assertEquals($type, $result->getType());
        $this->assertEquals(6, $result->getSequence());
        $this->assertSame($task, $result->getTask());
    }

    public function testAddCondition_addsConditionToNode(): void
    {
        // Arrange
        $node = $this->createMock(Node::class);
        $name = 'Test Condition';
        $field = 'field1';
        $operator = ConditionOperator::EQUAL; // 修正为EQUAL而不是EQUALS
        $value = 'value1';
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function(NodeCondition $condition) use ($name, $field, $operator, $value, $node) {
                return $condition->getName() === $name && 
                       $condition->getField() === $field && 
                       $condition->getOperator() === $operator && 
                       $condition->getValue() === $value &&
                       $condition->getNode() === $node;
            }));
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->nodeService->addCondition($node, $name, $field, $operator, $value);

        // Assert
        $this->assertEquals($name, $result->getName());
        $this->assertEquals($field, $result->getField());
        $this->assertEquals($operator, $result->getOperator());
        $this->assertEquals($value, $result->getValue());
        $this->assertSame($node, $result->getNode());
    }

    public function testSetDelay_whenNodeHasNoDelay_createsNewDelay(): void
    {
        // Arrange
        $node = $this->createMock(Node::class);
        $node->method('getDelay')->willReturn(null);
        
        $type = DelayType::MINUTES;
        $value = '30';
        
        $this->entityManager->expects($this->once())
            ->method('persist');
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->nodeService->setDelay($node, $type, $value);

        // Assert
        $this->assertInstanceOf(NodeDelay::class, $result);
        $this->assertEquals($type, $result->getType());
        $this->assertEquals($value, $result->getValue());
        $this->assertSame($node, $result->getNode());
    }

    public function testSetDelay_whenNodeHasExistingDelay_updatesDelay(): void
    {
        // Arrange
        $existingDelay = $this->createMock(NodeDelay::class);
        
        $node = $this->createMock(Node::class);
        $node->method('getDelay')->willReturn($existingDelay);
        
        $type = DelayType::HOURS;
        $value = '2';
        
        $existingDelay->expects($this->once())
            ->method('setType')
            ->with($type)
            ->willReturnSelf();
            
        $existingDelay->expects($this->once())
            ->method('setValue')
            ->with($value)
            ->willReturnSelf();
            
        $existingDelay->expects($this->once())
            ->method('setNode')
            ->with($node)
            ->willReturnSelf();
        
        $this->entityManager->expects($this->never())
            ->method('persist');
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->nodeService->setDelay($node, $type, $value);

        // Assert
        $this->assertSame($existingDelay, $result);
    }

    public function testUpdateSequence_withValidSequence_updatesNodeSequence(): void
    {
        // Arrange
        $node = $this->createMock(Node::class);
        $node->method('getSequence')->willReturn(3);
        $node->expects($this->once())
            ->method('setSequence')
            ->with(5);
            
        $node1 = $this->createMock(Node::class);
        $node1->method('getSequence')->willReturn(4);
        $node1->expects($this->once())
            ->method('setSequence')
            ->with(3);
            
        $node2 = $this->createMock(Node::class);
        $node2->method('getSequence')->willReturn(5);
        $node2->expects($this->once())
            ->method('setSequence')
            ->with(4);
        
        // 创建一个真实的TestArrayCollection，其中包含了所有节点
        $nodesArray = [$node, $node1, $node2];
        $nodes = new TestArrayCollection($nodesArray);
        
        $task = $this->createMock(Task::class);
        $task->method('getNodes')->willReturn($nodes);
        
        $node->method('getTask')->willReturn($task);
        $node->method('getType')->willReturn(NodeType::RESOURCE); // 使用正确的枚举值
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->nodeService->updateSequence($node, 5);
        
        // 由于方法没有返回值，我们简单断言true以通过测试
        $this->assertTrue(true);
    }

    public function testUpdateSequence_withSequenceLessThanOne_throwsException(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        
        $node = $this->createMock(Node::class);
        $node->method('getTask')->willReturn($task);
        
        $this->expectException(\MarketingPlanBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sequence must be greater than 0');

        // Act
        $this->nodeService->updateSequence($node, 0);
    }

    public function testUpdateSequence_withSequenceGreaterThanMax_throwsException(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        
        $node = $this->createMock(Node::class);
        $node->method('getTask')->willReturn($task);
        
        // 创建一个带有自定义max方法的集合
        $node1 = $this->createMock(Node::class);
        $node1->method('getSequence')->willReturn(1);
        
        $node2 = $this->createMock(Node::class);
        $node2->method('getSequence')->willReturn(2);
        
        $node3 = $this->createMock(Node::class);
        $node3->method('getSequence')->willReturn(3);
        
        $nodes = new TestArrayCollection([$node1, $node2, $node3]);
        
        $task->method('getNodes')->willReturn($nodes);
        
        $this->expectException(\MarketingPlanBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sequence must be less than or equal to 3');

        // Act
        $this->nodeService->updateSequence($node, 4);
    }

    public function testUpdateSequence_withStartNodeNotFirst_throwsException(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        
        $node = $this->createMock(Node::class);
        $node->method('getTask')->willReturn($task);
        $node->method('getType')->willReturn(NodeType::START);
        
        // 创建一个带有自定义max方法的集合
        $node1 = $this->createMock(Node::class);
        $node1->method('getSequence')->willReturn(1);
        
        $node2 = $this->createMock(Node::class);
        $node2->method('getSequence')->willReturn(2);
        
        $node3 = $this->createMock(Node::class);
        $node3->method('getSequence')->willReturn(3);
        
        $nodes = new TestArrayCollection([$node1, $node2, $node3]);
        
        $task->method('getNodes')->willReturn($nodes);
        
        $this->expectException(\MarketingPlanBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('START node must be the first node');

        // Act
        $this->nodeService->updateSequence($node, 2);
    }

    public function testUpdateSequence_withEndNodeNotLast_throwsException(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        
        $node = $this->createMock(Node::class);
        $node->method('getTask')->willReturn($task);
        $node->method('getType')->willReturn(NodeType::END);
        
        // 创建一个带有自定义max方法的集合
        $node1 = $this->createMock(Node::class);
        $node1->method('getSequence')->willReturn(1);
        
        $node2 = $this->createMock(Node::class);
        $node2->method('getSequence')->willReturn(2);
        
        $node3 = $this->createMock(Node::class);
        $node3->method('getSequence')->willReturn(3);
        
        $nodes = new TestArrayCollection([$node1, $node2, $node3]);
        
        $task->method('getNodes')->willReturn($nodes);
        
        $this->expectException(\MarketingPlanBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('END node must be the last node');

        // Act
        $this->nodeService->updateSequence($node, 2);
    }

    public function testDelete_withRegularNode_removesNodeAndUpdatesSequences(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        
        $node = $this->createMock(Node::class);
        $node->method('getTask')->willReturn($task);
        $node->method('getType')->willReturn(NodeType::RESOURCE); // 使用正确的枚举值
        $node->method('getSequence')->willReturn(2);
        
        $node1 = $this->createMock(Node::class);
        $node1->method('getSequence')->willReturn(3);
        $node1->expects($this->once())
            ->method('setSequence')
            ->with(2);
            
        $node2 = $this->createMock(Node::class);
        $node2->method('getSequence')->willReturn(4);
        $node2->expects($this->once())
            ->method('setSequence')
            ->with(3);
        
        // 创建一个真实的ArrayCollection，其中包含了所有节点
        $nodesArray = [$node, $node1, $node2];
        $nodes = new ArrayCollection($nodesArray);
        
        $task->method('getNodes')->willReturn($nodes);
        
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($node);
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->nodeService->delete($node);
        
        // 由于方法没有返回值，断言测试通过
        $this->assertTrue(true);
    }

    public function testDelete_withStartNode_throwsException(): void
    {
        // Arrange
        $node = $this->createMock(Node::class);
        $node->method('getType')->willReturn(NodeType::START);
        
        $this->expectException(\MarketingPlanBundle\Exception\NodeException::class);
        $this->expectExceptionMessage('Cannot delete START node');

        // Act
        $this->nodeService->delete($node);
    }

    public function testDelete_withEndNode_throwsException(): void
    {
        // Arrange
        $node = $this->createMock(Node::class);
        $node->method('getType')->willReturn(NodeType::END);
        
        $this->expectException(\MarketingPlanBundle\Exception\NodeException::class);
        $this->expectExceptionMessage('Cannot delete END node');

        // Act
        $this->nodeService->delete($node);
    }
} 