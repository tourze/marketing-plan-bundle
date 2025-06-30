<?php

namespace MarketingPlanBundle\Tests\Entity;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\TaskStatus;
use PHPUnit\Framework\TestCase;
use Tourze\UserTagContracts\TagInterface;

class TaskTest extends TestCase
{
    public function testToString_returnsTitle(): void
    {
        // Arrange
        $task = new Task();
        $title = '测试任务';
        $task->setTitle($title);

        // Act
        $result = (string) $task;

        // Assert
        $this->assertEquals($title, $result);
    }

    public function testSettersAndGetters(): void
    {
        // Arrange
        $task = new Task();
        $title = '任务标题';
        $description = '任务描述';
        $status = TaskStatus::DRAFT;
        $crowd = $this->createMock(TagInterface::class);
        $startTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $endTime = new \DateTimeImmutable('2024-01-31 23:59:59');

        // Act
        $task->setTitle($title)
             ->setDescription($description)
             ->setStatus($status)
             ->setCrowd($crowd)
             ->setStartTime($startTime)
             ->setEndTime($endTime);

        // Assert
        $this->assertEquals($title, $task->getTitle());
        $this->assertEquals($description, $task->getDescription());
        $this->assertEquals($status, $task->getStatus());
        $this->assertSame($crowd, $task->getCrowd());
        $this->assertEquals($startTime, $task->getStartTime());
        $this->assertEquals($endTime, $task->getEndTime());
    }

    public function testAddAndRemoveNode(): void
    {
        // Arrange
        $task = new Task();
        $node = $this->createMock(Node::class);
        
        // Configure mock - setTask will be called twice: once for add, once for remove
        $node->expects($this->exactly(2))
             ->method('setTask');

        // Act & Assert - Add node
        $task->addNode($node);
        $this->assertTrue($task->getNodes()->contains($node));

        // Act & Assert - Remove node
        $node->expects($this->once())
             ->method('getTask')
             ->willReturn($task);
             
        $task->removeNode($node);
        $this->assertFalse($task->getNodes()->contains($node));
    }
} 