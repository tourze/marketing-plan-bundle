<?php

namespace MarketingPlanBundle\Tests\Service;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\TaskStatus;
use MarketingPlanBundle\Exception\TaskException;
use MarketingPlanBundle\Service\TaskService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;

/**
 * @internal
 */
#[CoversClass(TaskService::class)]
#[RunTestsInSeparateProcesses]
final class TaskServiceTest extends AbstractIntegrationTestCase
{
    private TaskService $taskService;

    protected function onSetUp(): void
    {
        $service = self::getContainer()->get(TaskService::class);
        $this->assertInstanceOf(TaskService::class, $service);
        $this->taskService = $service;
    }

    public function testTaskServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(TaskService::class, $this->taskService);
    }

    public function testCreateCreatesTaskWithStartAndEndNodes(): void
    {
        $crowd = $this->createTag('Test Crowd 1');
        $startTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $endTime = new \DateTimeImmutable('2024-01-01 18:00:00');

        $task = $this->taskService->create('Test Task', $crowd, $startTime, $endTime);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertSame('Test Task', $task->getTitle());
        $this->assertSame($crowd, $task->getCrowd());
        $this->assertEquals($startTime, $task->getStartTime());
        $this->assertEquals($endTime, $task->getEndTime());
        $this->assertSame(TaskStatus::DRAFT, $task->getStatus());
        $this->assertNotNull($task->getId());

        // Verify start and end nodes are created
        $nodes = $task->getNodes();
        $this->assertCount(2, $nodes);

        $startNode = $nodes->first();
        $endNode = $nodes->last();

        $this->assertInstanceOf(Node::class, $startNode);
        $this->assertInstanceOf(Node::class, $endNode);

        $this->assertSame('开始', $startNode->getName());
        $this->assertSame(NodeType::START, $startNode->getType());
        $this->assertSame(1, $startNode->getSequence());
        $this->assertSame($task, $startNode->getTask());

        $this->assertSame('结束', $endNode->getName());
        $this->assertSame(NodeType::END, $endNode->getType());
        $this->assertSame(999, $endNode->getSequence());
        $this->assertSame($task, $endNode->getTask());
    }

    public function testCreateWithDifferentTimeFormats(): void
    {
        $crowd = $this->createTag('Test Crowd 1');
        $startTime = new \DateTime('2024-02-01 09:00:00');
        $endTime = new \DateTime('2024-02-01 17:00:00');

        $task = $this->taskService->create('Another Task', $crowd, $startTime, $endTime);

        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getStartTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getEndTime());
        $this->assertEquals($startTime->format('Y-m-d H:i:s'), $task->getStartTime()->format('Y-m-d H:i:s'));
        $this->assertEquals($endTime->format('Y-m-d H:i:s'), $task->getEndTime()->format('Y-m-d H:i:s'));
    }

    public function testPublishSucceedsWithValidTask(): void
    {
        $task = $this->createValidTask();

        $this->taskService->publish($task);

        $this->assertSame(TaskStatus::RUNNING, $task->getStatus());
    }

    public function testPublishThrowsExceptionWhenMissingStartNode(): void
    {
        $task = $this->createTaskWithoutNodes();

        // Add only end node
        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $endNode = new Node();
        $endNode->setName('End');
        $endNode->setType(NodeType::END);
        $endNode->setSequence(1);
        $endNode->setTask($task);
        $endNode->setResource($resourceConfig);
        $task->addNode($endNode);
        self::getEntityManager()->persist($endNode);
        self::getEntityManager()->flush();

        $this->expectException(TaskException::class);
        $this->expectExceptionMessage('Task must have START and END nodes');

        $this->taskService->publish($task);
    }

    public function testPublishThrowsExceptionWhenMissingEndNode(): void
    {
        $task = $this->createTaskWithoutNodes();

        // Add only start node
        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $startNode = new Node();
        $startNode->setName('Start');
        $startNode->setType(NodeType::START);
        $startNode->setSequence(1);
        $startNode->setTask($task);
        $startNode->setResource($resourceConfig);
        $task->addNode($startNode);
        self::getEntityManager()->persist($startNode);
        self::getEntityManager()->flush();

        $this->expectException(TaskException::class);
        $this->expectExceptionMessage('Task must have START and END nodes');

        $this->taskService->publish($task);
    }

    public function testPublishThrowsExceptionWhenSequencesNotContinuous(): void
    {
        $task = $this->createTaskWithoutNodes();

        // Create nodes with non-continuous sequences (1, 3, 5)
        $startResourceConfig = new ResourceConfig();
        $startResourceConfig->setType('none');
        $startResourceConfig->setAmount(0);

        $startNode = new Node();
        $startNode->setName('Start');
        $startNode->setType(NodeType::START);
        $startNode->setSequence(1);
        $startNode->setTask($task);
        $startNode->setResource($startResourceConfig);

        $middleResourceConfig = new ResourceConfig();
        $middleResourceConfig->setType('none');
        $middleResourceConfig->setAmount(0);

        $middleNode = new Node();
        $middleNode->setName('Middle');
        $middleNode->setType(NodeType::RESOURCE);
        $middleNode->setSequence(3);
        $middleNode->setTask($task);
        $middleNode->setResource($middleResourceConfig);

        $endResourceConfig = new ResourceConfig();
        $endResourceConfig->setType('none');
        $endResourceConfig->setAmount(0);

        $endNode = new Node();
        $endNode->setName('End');
        $endNode->setType(NodeType::END);
        $endNode->setSequence(5);
        $endNode->setTask($task);
        $endNode->setResource($endResourceConfig);

        // 将节点添加到任务
        $task->addNode($startNode);
        $task->addNode($middleNode);
        $task->addNode($endNode);

        self::getEntityManager()->persist($startNode);
        self::getEntityManager()->persist($middleNode);
        self::getEntityManager()->persist($endNode);
        self::getEntityManager()->flush();

        $this->expectException(TaskException::class);
        $this->expectExceptionMessage('Node sequences must be continuous');

        $this->taskService->publish($task);
    }

    public function testPauseSucceedsWhenTaskIsRunning(): void
    {
        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::RUNNING);
        self::getEntityManager()->flush();

        $this->taskService->pause($task);

        $this->assertSame(TaskStatus::PAUSED, $task->getStatus());
    }

    public function testPauseThrowsExceptionWhenTaskNotRunning(): void
    {
        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::DRAFT);
        self::getEntityManager()->flush();

        $this->expectException(TaskException::class);
        $this->expectExceptionMessage('Task is not running');

        $this->taskService->pause($task);
    }

    public function testResumeSucceedsWhenTaskIsPaused(): void
    {
        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::PAUSED);
        self::getEntityManager()->flush();

        $this->taskService->resume($task);

        $this->assertSame(TaskStatus::RUNNING, $task->getStatus());
    }

    public function testResumeThrowsExceptionWhenTaskNotPaused(): void
    {
        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::DRAFT);
        self::getEntityManager()->flush();

        $this->expectException(TaskException::class);
        $this->expectExceptionMessage('Task is not paused');

        $this->taskService->resume($task);
    }

    public function testFinishSucceedsWhenTaskIsRunning(): void
    {
        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::RUNNING);
        self::getEntityManager()->flush();

        $this->taskService->finish($task);

        $this->assertSame(TaskStatus::FINISHED, $task->getStatus());
    }

    public function testFinishThrowsExceptionWhenTaskNotRunning(): void
    {
        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::DRAFT);
        self::getEntityManager()->flush();

        $this->expectException(TaskException::class);
        $this->expectExceptionMessage('Task is not running');

        $this->taskService->finish($task);
    }

    public function testCheckStatusStartsTaskWhenTimeArrives(): void
    {
        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::DRAFT);
        $task->setStartTime(new \DateTimeImmutable('-1 hour')); // Past time
        self::getEntityManager()->flush();

        $this->taskService->checkStatus($task);

        $this->assertSame(TaskStatus::RUNNING, $task->getStatus());
    }

    public function testCheckStatusFinishesTaskWhenEndTimeArrives(): void
    {
        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::RUNNING);
        $task->setEndTime(new \DateTimeImmutable('-1 hour')); // Past time
        self::getEntityManager()->flush();

        $this->taskService->checkStatus($task);

        $this->assertSame(TaskStatus::FINISHED, $task->getStatus());
    }

    public function testCheckStatusDoesNothingWhenNotTimeYet(): void
    {
        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::DRAFT);
        $task->setStartTime(new \DateTimeImmutable('+1 hour')); // Future time
        self::getEntityManager()->flush();

        $this->taskService->checkStatus($task);

        $this->assertSame(TaskStatus::DRAFT, $task->getStatus());
    }

    public function testCheckStatusDoesNothingWhenRunningAndNotEndTime(): void
    {
        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::RUNNING);
        $task->setEndTime(new \DateTimeImmutable('+1 hour')); // Future time
        self::getEntityManager()->flush();

        $this->taskService->checkStatus($task);

        $this->assertSame(TaskStatus::RUNNING, $task->getStatus());
    }

    public function testCheckStatusDoesNothingForFinishedTask(): void
    {
        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::FINISHED);
        self::getEntityManager()->flush();

        $this->taskService->checkStatus($task);

        $this->assertSame(TaskStatus::FINISHED, $task->getStatus());
    }

    public function testCheckStatusDoesNothingForPausedTask(): void
    {
        $task = $this->createValidTask();
        $task->setStatus(TaskStatus::PAUSED);
        $task->setEndTime(new \DateTimeImmutable('-1 hour')); // Past end time
        self::getEntityManager()->flush();

        $this->taskService->checkStatus($task);

        $this->assertSame(TaskStatus::PAUSED, $task->getStatus());
    }

    private function createValidTask(): Task
    {
        $crowd = $this->createTag('Test Crowd 1');
        $task = new Task();
        $task->setTitle('Valid Task');
        $task->setDescription('Valid Task Description');
        $task->setStatus(TaskStatus::DRAFT);
        $task->setCrowd($crowd);
        $task->setStartTime(new \DateTimeImmutable());
        $task->setEndTime(new \DateTimeImmutable('+1 day'));

        self::getEntityManager()->persist($task);

        // Create start and end nodes with continuous sequences
        $startResourceConfig = new ResourceConfig();
        $startResourceConfig->setType('none');
        $startResourceConfig->setAmount(0);

        $startNode = new Node();
        $startNode->setName('Start');
        $startNode->setType(NodeType::START);
        $startNode->setSequence(1);
        $startNode->setTask($task);
        $startNode->setResource($startResourceConfig);

        $endResourceConfig = new ResourceConfig();
        $endResourceConfig->setType('none');
        $endResourceConfig->setAmount(0);

        $endNode = new Node();
        $endNode->setName('End');
        $endNode->setType(NodeType::END);
        $endNode->setSequence(2);
        $endNode->setTask($task);
        $endNode->setResource($endResourceConfig);

        // 将节点添加到任务
        $task->addNode($startNode);
        $task->addNode($endNode);

        self::getEntityManager()->persist($startNode);
        self::getEntityManager()->persist($endNode);
        self::getEntityManager()->flush();

        return $task;
    }

    private function createTaskWithoutNodes(): Task
    {
        $crowd = $this->createTag('Test Crowd 1');
        $task = new Task();
        $task->setTitle('Task Without Nodes');
        $task->setDescription('Task Without Nodes Description');
        $task->setStatus(TaskStatus::DRAFT);
        $task->setCrowd($crowd);
        $task->setStartTime(new \DateTimeImmutable());
        $task->setEndTime(new \DateTimeImmutable('+1 day'));

        self::getEntityManager()->persist($task);
        self::getEntityManager()->flush();

        return $task;
    }

    private function createTag(string $name): Tag
    {
        $catalogType = new CatalogType();
        $catalogType->setName('test-type');
        $catalogType->setCode('test_type');
        self::getEntityManager()->persist($catalogType);

        $catalog = new Catalog();
        $catalog->setName('Test Category');
        $catalog->setType($catalogType);
        $catalog->setEnabled(true);
        self::getEntityManager()->persist($catalog);

        $tag = new Tag();
        $tag->setName($name);
        $tag->setType(TagType::StaticTag);
        $tag->setCatalog($catalog);
        $tag->setValid(true);
        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        return $tag;
    }
}
