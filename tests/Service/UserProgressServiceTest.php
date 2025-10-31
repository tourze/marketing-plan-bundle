<?php

namespace MarketingPlanBundle\Tests\Service;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeStage;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Entity\UserProgress;
use MarketingPlanBundle\Enum\DropReason;
use MarketingPlanBundle\Enum\NodeStageStatus;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\ProgressStatus;
use MarketingPlanBundle\Enum\TaskStatus;
use MarketingPlanBundle\Exception\UserProgressException;
use MarketingPlanBundle\Service\UserProgressService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;
use UserTagBundle\Entity\Tag;

/**
 * @internal
 */
#[CoversClass(UserProgressService::class)]
#[RunTestsInSeparateProcesses]
final class UserProgressServiceTest extends AbstractIntegrationTestCase
{
    private UserProgressService $userProgressService;

    protected function onSetUp(): void
    {
        $service = self::getContainer()->get(UserProgressService::class);
        $this->assertInstanceOf(UserProgressService::class, $service);
        $this->userProgressService = $service;
    }

    public function testUserProgressServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(UserProgressService::class, $this->userProgressService);
    }

    public function testCreateCreatesNewUserProgressWithFirstNode(): void
    {
        $task = $this->createTaskWithNodes();
        $userId = 'user123';

        $progress = $this->userProgressService->create($task, $userId);

        $this->assertInstanceOf(UserProgress::class, $progress);
        $this->assertSame($task, $progress->getTask());
        $this->assertSame($userId, $progress->getUserId());
        $this->assertSame(ProgressStatus::RUNNING, $progress->getStatus());
        $this->assertNotNull($progress->getStartTime());
        $this->assertNotNull($progress->getId());

        // Check that current node is the first node
        $firstNode = $task->getNodes()->first();
        $this->assertInstanceOf(Node::class, $firstNode);
        $this->assertInstanceOf(Node::class, $firstNode);
        $this->assertSame($firstNode, $progress->getCurrentNode());

        // Check that first node stage is created
        $stages = $progress->getStages();
        $this->assertCount(1, $stages);

        $firstStage = $stages->first();
        $this->assertInstanceOf(NodeStage::class, $firstStage);
        $this->assertSame($firstNode, $firstStage->getNode());
        $this->assertSame(NodeStageStatus::RUNNING, $firstStage->getStatus());
        $this->assertNotNull($firstStage->getReachTime());
    }

    public function testCreateReturnsExistingProgressIfAlreadyExists(): void
    {
        $task = $this->createTaskWithNodes();
        $userId = 'user123';

        // Create first progress
        $progress1 = $this->userProgressService->create($task, $userId);
        $progress1Id = $progress1->getId();

        // Try to create again - should return existing
        $progress2 = $this->userProgressService->create($task, $userId);

        $this->assertSame($progress1Id, $progress2->getId());
        $this->assertSame($progress1, $progress2);
    }

    public function testCreateThrowsExceptionWhenTaskHasNoNodes(): void
    {
        $task = $this->createTaskWithoutNodes();
        $userId = 'user123';

        $this->expectException(UserProgressException::class);
        $this->expectExceptionMessage('Task has no nodes');

        $this->userProgressService->create($task, $userId);
    }

    public function testMarkTouchedSetsNodeStageAsTouched(): void
    {
        $progress = $this->createUserProgressWithStage();
        $node = $progress->getCurrentNode();

        $this->userProgressService->markTouched($progress, $node);

        $stage = $progress->getNodeStage($node);
        $this->assertNotNull($stage);
        $this->assertTrue($stage->isTouched());
        $this->assertNotNull($stage->getTouchTime());
    }

    public function testMarkTouchedDoesNothingIfAlreadyTouched(): void
    {
        $progress = $this->createUserProgressWithStage();
        $node = $progress->getCurrentNode();

        // Mark as touched first time
        $this->userProgressService->markTouched($progress, $node);
        $stage = $progress->getNodeStage($node);
        $this->assertNotNull($stage);
        $firstTouchTime = $stage->getTouchTime();

        // Mark as touched second time
        $this->userProgressService->markTouched($progress, $node);

        // Touch time should remain the same
        $this->assertNotNull($stage);
        $this->assertEquals($firstTouchTime, $stage->getTouchTime());
    }

    public function testMarkTouchedThrowsExceptionWhenStageNotFound(): void
    {
        $progress = $this->createUserProgressWithStage();
        $otherNode = $this->createNode($progress->getTask(), 'Other Node', NodeType::RESOURCE, 99);

        $this->expectException(UserProgressException::class);
        $this->expectExceptionMessage('Node stage not found');

        $this->userProgressService->markTouched($progress, $otherNode);
    }

    public function testMarkActivatedSetsNodeStageAsActivated(): void
    {
        $progress = $this->createUserProgressWithStage();
        $node = $progress->getCurrentNode();

        $this->userProgressService->markActivated($progress, $node);

        $stage = $progress->getNodeStage($node);
        $this->assertNotNull($stage);
        $this->assertTrue($stage->isActivated());
        $this->assertNotNull($stage->getActiveTime());
    }

    public function testMarkActivatedDoesNothingIfAlreadyActivated(): void
    {
        $progress = $this->createUserProgressWithStage();
        $node = $progress->getCurrentNode();

        // Mark as activated first time
        $this->userProgressService->markActivated($progress, $node);
        $stage = $progress->getNodeStage($node);
        $this->assertNotNull($stage);
        $firstActiveTime = $stage->getActiveTime();

        // Mark as activated second time
        $this->userProgressService->markActivated($progress, $node);

        // Active time should remain the same
        $this->assertNotNull($stage);
        $this->assertEquals($firstActiveTime, $stage->getActiveTime());
    }

    public function testMarkActivatedFinishesProgressWhenEndNode(): void
    {
        $task = $this->createTaskWithNodes();
        $progress = $this->createUserProgress($task, 'user123');

        // Get the end node
        $endNode = $task->getNodes()->last();
        $this->assertInstanceOf(Node::class, $endNode);

        // Create stage for end node
        $endStage = new NodeStage();
        $endStage->setNode($endNode);
        $endStage->setUserProgress($progress);
        $endStage->setStatus(NodeStageStatus::RUNNING);
        $endStage->setReachTime(new \DateTimeImmutable());
        $progress->addStage($endStage);
        self::getEntityManager()->persist($endStage);
        self::getEntityManager()->flush();

        $this->userProgressService->markActivated($progress, $endNode);

        $this->assertSame(ProgressStatus::FINISHED, $progress->getStatus());
        $this->assertNotNull($progress->getFinishTime());
    }

    public function testMarkActivatedThrowsExceptionWhenStageNotFound(): void
    {
        $progress = $this->createUserProgressWithStage();
        $otherNode = $this->createNode($progress->getTask(), 'Other Node', NodeType::RESOURCE, 99);

        $this->expectException(UserProgressException::class);
        $this->expectExceptionMessage('Node stage not found');

        $this->userProgressService->markActivated($progress, $otherNode);
    }

    public function testMarkDroppedSetsNodeStageAsDropped(): void
    {
        $progress = $this->createUserProgressWithStage();
        $node = $progress->getCurrentNode();

        $this->userProgressService->markDropped($progress, $node, DropReason::TIMEOUT);

        $stage = $progress->getNodeStage($node);
        $this->assertNotNull($stage);
        $this->assertTrue($stage->isDropped());
        $this->assertNotNull($stage->getDropTime());
        $this->assertSame(DropReason::TIMEOUT, $stage->getDropReason());
        $this->assertSame(NodeStageStatus::DROPPED, $stage->getStatus());
        $this->assertSame(ProgressStatus::DROPPED, $progress->getStatus());
    }

    public function testMarkDroppedDoesNothingIfAlreadyDropped(): void
    {
        $progress = $this->createUserProgressWithStage();
        $node = $progress->getCurrentNode();

        // Mark as dropped first time
        $this->userProgressService->markDropped($progress, $node, DropReason::TIMEOUT);
        $stage = $progress->getNodeStage($node);
        $this->assertNotNull($stage);
        $firstDropTime = $stage->getDropTime();

        // Mark as dropped second time
        $this->userProgressService->markDropped($progress, $node, DropReason::CONDITION_NOT_MET);

        // Drop time and reason should remain the same
        $this->assertNotNull($stage);
        $this->assertEquals($firstDropTime, $stage->getDropTime());
        $this->assertSame(DropReason::TIMEOUT, $stage->getDropReason());
    }

    public function testMarkDroppedThrowsExceptionWhenStageNotFound(): void
    {
        $progress = $this->createUserProgressWithStage();
        $otherNode = $this->createNode($progress->getTask(), 'Other Node', NodeType::RESOURCE, 99);

        $this->expectException(UserProgressException::class);
        $this->expectExceptionMessage('Node stage not found');

        $this->userProgressService->markDropped($progress, $otherNode, DropReason::TIMEOUT);
    }

    public function testMoveToNextNodeCreatesNewStageForNextNode(): void
    {
        $task = $this->createTaskWithMultipleNodes();
        $progress = $this->createUserProgress($task, 'user123');

        $firstNode = $task->getNodes()->first();
        $this->assertInstanceOf(Node::class, $firstNode);
        $secondNode = $task->getNodes()->toArray()[1];
        $this->assertInstanceOf(Node::class, $secondNode);

        // Create and activate first stage
        $firstStage = new NodeStage();
        $firstStage->setNode($firstNode);
        $firstStage->setUserProgress($progress);
        $firstStage->setStatus(NodeStageStatus::RUNNING);
        $firstStage->setReachTime(new \DateTimeImmutable());
        $firstStage->setActiveTime(new \DateTimeImmutable());
        $progress->addStage($firstStage);
        $progress->setCurrentNode($firstNode);
        self::getEntityManager()->persist($firstStage);
        self::getEntityManager()->flush();

        $this->userProgressService->moveToNextNode($progress);

        // Check that current node is updated
        $this->assertSame($secondNode, $progress->getCurrentNode());

        // Check that new stage is created
        $secondStage = $progress->getNodeStage($secondNode);
        $this->assertNotNull($secondStage);
        $this->assertSame(NodeStageStatus::RUNNING, $secondStage->getStatus());
        $this->assertNotNull($secondStage->getReachTime());
    }

    public function testMoveToNextNodeThrowsExceptionWhenCurrentNodeNotActivated(): void
    {
        $task = $this->createTaskWithMultipleNodes();
        $progress = $this->createUserProgress($task, 'user123');

        $firstNode = $task->getNodes()->first();
        $this->assertInstanceOf(Node::class, $firstNode);

        // Create stage but don't activate it
        $firstStage = new NodeStage();
        $firstStage->setNode($firstNode);
        $firstStage->setUserProgress($progress);
        $firstStage->setStatus(NodeStageStatus::RUNNING);
        $firstStage->setReachTime(new \DateTimeImmutable());
        $progress->addStage($firstStage);
        $progress->setCurrentNode($firstNode);
        self::getEntityManager()->persist($firstStage);
        self::getEntityManager()->flush();

        $this->expectException(UserProgressException::class);
        $this->expectExceptionMessage('Current node not activated');

        $this->userProgressService->moveToNextNode($progress);
    }

    public function testMoveToNextNodeThrowsExceptionWhenNoNextNode(): void
    {
        $task = $this->createTaskWithNodes();
        $progress = $this->createUserProgress($task, 'user123');

        $lastNode = $task->getNodes()->last();
        $this->assertInstanceOf(Node::class, $lastNode);

        // Create and activate stage for last node
        $lastStage = new NodeStage();
        $lastStage->setNode($lastNode);
        $lastStage->setUserProgress($progress);
        $lastStage->setStatus(NodeStageStatus::RUNNING);
        $lastStage->setReachTime(new \DateTimeImmutable());
        $lastStage->setActiveTime(new \DateTimeImmutable());
        $progress->addStage($lastStage);
        $progress->setCurrentNode($lastNode);
        self::getEntityManager()->persist($lastStage);
        self::getEntityManager()->flush();

        $this->expectException(UserProgressException::class);
        $this->expectExceptionMessage('No next node found');

        $this->userProgressService->moveToNextNode($progress);
    }

    public function testCheckTimeoutDroppedMarksStagesAsDropped(): void
    {
        $task = $this->createTaskWithNodes();
        $node = $task->getNodes()->first();
        $this->assertInstanceOf(Node::class, $node);

        // Create progress and stage that should be dropped (old reach time)
        $progress = $this->createUserProgress($task, 'user123');
        $stage = new NodeStage();
        $stage->setNode($node);
        $stage->setUserProgress($progress);
        $stage->setStatus(NodeStageStatus::RUNNING);
        $stage->setReachTime(new \DateTimeImmutable('-2 hours'));
        $stage->setTouchTime(new \DateTimeImmutable('-2 hours'));  // 设置触达时间以满足查询条件
        $progress->addStage($stage);
        self::getEntityManager()->persist($stage);
        self::getEntityManager()->flush();

        $beforeTime = new \DateTimeImmutable('-1 hour');
        $this->userProgressService->checkTimeoutDropped($node, $beforeTime);

        self::getEntityManager()->refresh($progress);
        $this->assertSame(ProgressStatus::DROPPED, $progress->getStatus());

        self::getEntityManager()->refresh($stage);
        $this->assertTrue($stage->isDropped());
        $this->assertSame(DropReason::TIMEOUT, $stage->getDropReason());
    }

    public function testCheckConditionDroppedDoesNothingWithCurrentImplementation(): void
    {
        $progress = $this->createUserProgressWithStage();
        $node = $progress->getCurrentNode();

        // Set stage as touched but not activated
        $stage = $progress->getNodeStage($node);
        $this->assertNotNull($stage);
        $stage->setTouchTime(new \DateTimeImmutable());
        self::getEntityManager()->flush();

        $this->userProgressService->checkConditionDropped($progress, $node);

        // Since condition checking is not implemented, nothing should change
        $this->assertSame(ProgressStatus::RUNNING, $progress->getStatus());
        $this->assertFalse($stage->isDropped());
    }

    public function testCheckConditionDroppedSkipsWhenStageNotTouched(): void
    {
        $progress = $this->createUserProgressWithStage();
        $node = $progress->getCurrentNode();

        $this->userProgressService->checkConditionDropped($progress, $node);

        // Nothing should change
        $stage = $progress->getNodeStage($node);
        $this->assertNotNull($stage);
        $this->assertSame(ProgressStatus::RUNNING, $progress->getStatus());
        $this->assertFalse($stage->isDropped());
    }

    public function testCheckConditionDroppedSkipsWhenStageAlreadyActivated(): void
    {
        $progress = $this->createUserProgressWithStage();
        $node = $progress->getCurrentNode();

        $stage = $progress->getNodeStage($node);
        $this->assertNotNull($stage);
        $stage->setTouchTime(new \DateTimeImmutable());
        $stage->setActiveTime(new \DateTimeImmutable());
        self::getEntityManager()->flush();

        $this->userProgressService->checkConditionDropped($progress, $node);

        // Nothing should change
        $this->assertSame(ProgressStatus::RUNNING, $progress->getStatus());
        $this->assertFalse($stage->isDropped());
    }

    public function testCheckConditionDroppedSkipsWhenStageAlreadyDropped(): void
    {
        $progress = $this->createUserProgressWithStage();
        $node = $progress->getCurrentNode();

        $stage = $progress->getNodeStage($node);
        $this->assertNotNull($stage);
        $stage->setTouchTime(new \DateTimeImmutable());
        $stage->setDropTime(new \DateTimeImmutable());
        $stage->setDropReason(DropReason::TIMEOUT);
        $stage->setStatus(NodeStageStatus::DROPPED);
        self::getEntityManager()->flush();

        $this->userProgressService->checkConditionDropped($progress, $node);

        // Nothing should change
        $this->assertSame(ProgressStatus::RUNNING, $progress->getStatus());
        $this->assertSame(DropReason::TIMEOUT, $stage->getDropReason());
    }

    private function createTaskWithNodes(): Task
    {
        $crowd = new Tag();
        $crowd->setName('Test Crowd');
        self::getEntityManager()->persist($crowd);

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $task->setCrowd($crowd);
        $task->setStartTime(new \DateTimeImmutable());
        $task->setEndTime(new \DateTimeImmutable('+1 day'));

        self::getEntityManager()->persist($task);

        // Create start and end nodes
        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $startNode = new Node();
        $startNode->setName('Start');
        $startNode->setType(NodeType::START);
        $startNode->setSequence(1);
        $startNode->setTask($task);
        $startNode->setResource($resourceConfig);

        $endResourceConfig = new ResourceConfig();
        $endResourceConfig->setType('none');
        $endResourceConfig->setAmount(0);

        $endNode = new Node();
        $endNode->setName('End');
        $endNode->setType(NodeType::END);
        $endNode->setSequence(2);
        $endNode->setTask($task);
        $endNode->setResource($endResourceConfig);

        $task->addNode($startNode);
        $task->addNode($endNode);

        self::getEntityManager()->persist($startNode);
        self::getEntityManager()->persist($endNode);
        self::getEntityManager()->flush();

        return $task;
    }

    private function createTaskWithMultipleNodes(): Task
    {
        $crowd = new Tag();
        $crowd->setName('Multi Node Crowd');
        self::getEntityManager()->persist($crowd);

        $task = new Task();
        $task->setTitle('Multi Node Task');
        $task->setDescription('Task with multiple nodes');
        $task->setStatus(TaskStatus::DRAFT);
        $task->setCrowd($crowd);
        $task->setStartTime(new \DateTimeImmutable());
        $task->setEndTime(new \DateTimeImmutable('+1 day'));

        self::getEntityManager()->persist($task);

        // Create multiple nodes
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
        $middleNode->setSequence(2);
        $middleNode->setTask($task);
        $middleNode->setResource($middleResourceConfig);

        $endResourceConfig = new ResourceConfig();
        $endResourceConfig->setType('none');
        $endResourceConfig->setAmount(0);

        $endNode = new Node();
        $endNode->setName('End');
        $endNode->setType(NodeType::END);
        $endNode->setSequence(3);
        $endNode->setTask($task);
        $endNode->setResource($endResourceConfig);

        $task->addNode($startNode);
        $task->addNode($middleNode);
        $task->addNode($endNode);

        self::getEntityManager()->persist($startNode);
        self::getEntityManager()->persist($middleNode);
        self::getEntityManager()->persist($endNode);
        self::getEntityManager()->flush();

        return $task;
    }

    private function createTaskWithoutNodes(): Task
    {
        $crowd = new Tag();
        $crowd->setName('Empty Task Crowd');
        self::getEntityManager()->persist($crowd);

        $task = new Task();
        $task->setTitle('Empty Task');
        $task->setDescription('Task without nodes');
        $task->setStatus(TaskStatus::DRAFT);
        $task->setCrowd($crowd);
        $task->setStartTime(new \DateTimeImmutable());
        $task->setEndTime(new \DateTimeImmutable('+1 day'));

        self::getEntityManager()->persist($task);
        self::getEntityManager()->flush();

        return $task;
    }

    private function createUserProgress(Task $task, string $userId): UserProgress
    {
        $firstNode = $task->getNodes()->first();
        $this->assertInstanceOf(Node::class, $firstNode);

        $progress = new UserProgress();
        $progress->setTask($task);
        $progress->setUserId($userId);
        $progress->setCurrentNode($firstNode);
        $progress->setStatus(ProgressStatus::RUNNING);
        $progress->setStartTime(new \DateTimeImmutable());

        self::getEntityManager()->persist($progress);
        self::getEntityManager()->flush();

        return $progress;
    }

    private function createUserProgressWithStage(): UserProgress
    {
        $task = $this->createTaskWithNodes();
        $progress = $this->createUserProgress($task, 'user123');

        $firstNode = $task->getNodes()->first();
        $this->assertInstanceOf(Node::class, $firstNode);
        $stage = new NodeStage();
        $stage->setNode($firstNode);
        $stage->setUserProgress($progress);
        $stage->setStatus(NodeStageStatus::RUNNING);
        $stage->setReachTime(new \DateTimeImmutable());

        $progress->addStage($stage);
        self::getEntityManager()->persist($stage);
        self::getEntityManager()->flush();

        return $progress;
    }

    private function createNode(Task $task, string $name, NodeType $type, int $sequence): Node
    {
        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setName($name);
        $node->setType($type);
        $node->setSequence($sequence);
        $node->setTask($task);
        $node->setResource($resourceConfig);

        self::getEntityManager()->persist($node);
        self::getEntityManager()->flush();

        return $node;
    }
}
