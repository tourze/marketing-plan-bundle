<?php

namespace MarketingPlanBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\TaskStatus;
use MarketingPlanBundle\Service\TaskService;
use PHPUnit\Framework\TestCase;
use Tourze\UserTagContracts\TagInterface;

class TaskServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private TaskService $taskService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->taskService = new TaskService($this->entityManager);
    }

    public function testCreate_withValidData_createsTaskWithStartAndEndNodes(): void
    {
        // Arrange
        $title = 'Test Task';
        $crowd = $this->createMock(TagInterface::class);
        $startTime = new \DateTime('2023-01-01 00:00:00');
        $endTime = new \DateTime('2023-01-31 23:59:59');

        // PHPUnit 10不再支持withConsecutive，需要单独配置每次调用
        $this->entityManager->expects($this->exactly(3))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $task = $this->taskService->create($title, $crowd, $startTime, $endTime);

        // Assert
        $this->assertSame($title, $task->getTitle());
        $this->assertSame($crowd, $task->getCrowd());
        $this->assertEquals($startTime, $task->getStartTime());
        $this->assertEquals($endTime, $task->getEndTime());
        $this->assertEquals(TaskStatus::DRAFT, $task->getStatus());
    }

    public function testPublish_withValidTask_setsTaskToRunning(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        $startNode = $this->createMock(Node::class);
        $endNode = $this->createMock(Node::class);
        
        // 使用实际的ArrayCollection
        $nodesArray = [$startNode, $endNode];
        $nodes = new ArrayCollection($nodesArray);
            
        $startNode->method('getType')->willReturn(NodeType::START);
        $startNode->method('getSequence')->willReturn(1);
        $endNode->method('getType')->willReturn(NodeType::END);
        $endNode->method('getSequence')->willReturn(2);

        $task->method('getNodes')->willReturn($nodes);

        $task->expects($this->once())
            ->method('setStatus')
            ->with(TaskStatus::RUNNING);

        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->taskService->publish($task);
        
        // 确认测试通过
        $this->assertTrue(true);
    }

    public function testPublish_withoutStartNode_throwsException(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        $endNode = $this->createMock(Node::class);
        
        // 使用实际的ArrayCollection，只包含END节点
        $nodesArray = [$endNode];
        $nodes = new ArrayCollection($nodesArray);
            
        $endNode->method('getType')->willReturn(NodeType::END);
        
        $task->method('getNodes')->willReturn($nodes);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Task must have START and END nodes');

        // Act
        $this->taskService->publish($task);
    }

    public function testPublish_withoutEndNode_throwsException(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        $startNode = $this->createMock(Node::class);
        
        // 使用实际的ArrayCollection，只包含START节点
        $nodesArray = [$startNode];
        $nodes = new ArrayCollection($nodesArray);
            
        $startNode->method('getType')->willReturn(NodeType::START);
        
        $task->method('getNodes')->willReturn($nodes);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Task must have START and END nodes');

        // Act
        $this->taskService->publish($task);
    }

    public function testPublish_withDiscontinuousSequence_throwsException(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        $startNode = $this->createMock(Node::class);
        $endNode = $this->createMock(Node::class);
        
        // 使用实际的ArrayCollection
        $startNode->method('getType')->willReturn(NodeType::START);
        $startNode->method('getSequence')->willReturn(1);
        $endNode->method('getType')->willReturn(NodeType::END);
        $endNode->method('getSequence')->willReturn(3); // 有间隔的序号
        
        $nodesArray = [$startNode, $endNode];
        $nodes = new ArrayCollection($nodesArray);
        
        $task->method('getNodes')->willReturn($nodes);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Node sequences must be continuous');

        // Act
        $this->taskService->publish($task);
    }

    public function testPause_whenRunning_setsTaskToPaused(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        $task->method('getStatus')->willReturn(TaskStatus::RUNNING);
        
        $task->expects($this->once())
            ->method('setStatus')
            ->with(TaskStatus::PAUSED);

        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->taskService->pause($task);
        
        // 确认测试通过
        $this->assertTrue(true);
    }

    public function testPause_whenNotRunning_throwsException(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        $task->method('getStatus')->willReturn(TaskStatus::DRAFT);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Task is not running');

        // Act
        $this->taskService->pause($task);
    }

    public function testResume_whenPaused_setsTaskToRunning(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        $task->method('getStatus')->willReturn(TaskStatus::PAUSED);
        
        $task->expects($this->once())
            ->method('setStatus')
            ->with(TaskStatus::RUNNING);

        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->taskService->resume($task);
        
        // 确认测试通过
        $this->assertTrue(true);
    }

    public function testResume_whenNotPaused_throwsException(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        $task->method('getStatus')->willReturn(TaskStatus::RUNNING);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Task is not paused');

        // Act
        $this->taskService->resume($task);
    }

    public function testFinish_whenRunning_setsTaskToFinished(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        $task->method('getStatus')->willReturn(TaskStatus::RUNNING);
        
        $task->expects($this->once())
            ->method('setStatus')
            ->with(TaskStatus::FINISHED);

        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->taskService->finish($task);
        
        // 确认测试通过
        $this->assertTrue(true);
    }

    public function testFinish_whenNotRunning_throwsException(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        $task->method('getStatus')->willReturn(TaskStatus::PAUSED);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Task is not running');

        // Act
        $this->taskService->finish($task);
    }

    public function testCheckStatus_whenDraftAndStartTimeReached_publishesTask(): void
    {
        // Arrange
        // 创建部分模拟对象，同时允许自定义其他方法
        $task = $this->createPartialMock(Task::class, ['getStatus', 'getStartTime', 'getEndTime', 'setStatus', 'getNodes']);
        $task->method('getStatus')->willReturn(TaskStatus::DRAFT);
        $task->method('getStartTime')->willReturn(new \DateTimeImmutable('yesterday'));
        
        // 设置getNodes方法的行为
        $startNode = $this->createMock(Node::class);
        $endNode = $this->createMock(Node::class);
        
        $startNode->method('getType')->willReturn(NodeType::START);
        $startNode->method('getSequence')->willReturn(1);
        $endNode->method('getType')->willReturn(NodeType::END);
        $endNode->method('getSequence')->willReturn(2);
        
        // 使用实际的ArrayCollection
        $nodesArray = [$startNode, $endNode];
        $nodes = new ArrayCollection($nodesArray);
        
        $task->method('getNodes')->willReturn($nodes);

        $task->expects($this->once())
            ->method('setStatus')
            ->with(TaskStatus::RUNNING);

        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->taskService->checkStatus($task);
        
        // 确认测试通过
        $this->assertTrue(true);
    }

    public function testCheckStatus_whenRunningAndEndTimeReached_finishesTask(): void
    {
        // Arrange
        $task = $this->createPartialMock(Task::class, ['getStatus', 'getStartTime', 'getEndTime', 'setStatus']);
        $task->method('getStatus')->willReturn(TaskStatus::RUNNING);
        $task->method('getEndTime')->willReturn(new \DateTimeImmutable('yesterday'));
        
        $task->expects($this->once())
            ->method('setStatus')
            ->with(TaskStatus::FINISHED);

        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->taskService->checkStatus($task);
        
        // 确认测试通过
        $this->assertTrue(true);
    }

    public function testCheckStatus_whenRunningAndEndTimeNotReached_doesNothing(): void
    {
        // Arrange
        $task = $this->createPartialMock(Task::class, ['getStatus', 'getStartTime', 'getEndTime', 'setStatus']);
        $task->method('getStatus')->willReturn(TaskStatus::RUNNING);
        $task->method('getEndTime')->willReturn(new \DateTimeImmutable('tomorrow'));
        
        $task->expects($this->never())
            ->method('setStatus');

        $this->entityManager->expects($this->never())
            ->method('flush');

        // Act
        $this->taskService->checkStatus($task);
        
        // 确认测试通过
        $this->assertTrue(true);
    }
} 