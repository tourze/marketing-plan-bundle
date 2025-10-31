<?php

namespace MarketingPlanBundle\Tests\Entity;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\TaskStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Task::class)]
final class TaskTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Task();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'title' => ['title', '测试任务'];
        yield 'description' => ['description', '任务描述'];
        yield 'status' => ['status', TaskStatus::DRAFT];
        // crowd 属性需要接口实例，在独立进程中无法序列化测试，跳过此属性
        yield 'startTime' => ['startTime', new \DateTimeImmutable('2024-01-01 10:00:00')];
        yield 'endTime' => ['endTime', new \DateTimeImmutable('2024-01-31 23:59:59')];
    }

    public function testToStringReturnsTitle(): void
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

    public function testAddAndRemoveNode(): void
    {
        // Arrange
        $task = new Task();
        $node = $this->createMock(Node::class);

        // Configure mock - setTask will be called twice: once for add, once for remove
        $node->expects($this->exactly(2))
            ->method('setTask')
        ;

        // Act & Assert - Add node
        $task->addNode($node);
        $this->assertTrue($task->getNodes()->contains($node));

        // Act & Assert - Remove node
        $node->expects($this->once())
            ->method('getTask')
            ->willReturn($task)
        ;

        $task->removeNode($node);
        $this->assertFalse($task->getNodes()->contains($node));
    }
}
