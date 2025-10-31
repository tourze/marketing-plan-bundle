<?php

namespace MarketingPlanBundle\Tests\Repository;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeStage;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Entity\UserProgress;
use MarketingPlanBundle\Enum\NodeStageStatus;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\ProgressStatus;
use MarketingPlanBundle\Enum\TaskStatus;
use MarketingPlanBundle\Repository\NodeStageRepository;
use MarketingPlanBundle\Repository\UserProgressRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;
use UserTagBundle\Entity\Tag;

/**
 * @internal
 */
#[CoversClass(UserProgressRepository::class)]
#[RunTestsInSeparateProcesses]
final class UserProgressRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 可以在这里添加测试初始化逻辑
    }

    protected function getRepository(): UserProgressRepository
    {
        return self::getService(UserProgressRepository::class);
    }

    public function testSave(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $userProgress = new UserProgress();
        $userProgress->setTask($task);
        $userProgress->setUserId('user123');
        $userProgress->setCurrentNode($node);
        $userProgress->setStatus(ProgressStatus::RUNNING);
        $userProgress->setStartTime(new \DateTimeImmutable());

        $this->getRepository()->save($userProgress, true);

        $this->assertGreaterThan(0, $userProgress->getId());
        $this->assertSame($task, $userProgress->getTask());
        $this->assertSame('user123', $userProgress->getUserId());
        $this->assertSame($node, $userProgress->getCurrentNode());
        $this->assertSame(ProgressStatus::RUNNING, $userProgress->getStatus());
    }

    public function testRemove(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $userProgress = new UserProgress();
        $userProgress->setTask($task);
        $userProgress->setUserId('user123');
        $userProgress->setCurrentNode($node);
        $userProgress->setStatus(ProgressStatus::RUNNING);
        $userProgress->setStartTime(new \DateTimeImmutable());

        $this->getRepository()->save($userProgress, true);
        $progressId = $userProgress->getId();

        $this->getRepository()->remove($userProgress, true);

        $removedProgress = $this->getRepository()->find($progressId);
        $this->assertNull($removedProgress);
    }

    public function testFind(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $userProgress = new UserProgress();
        $userProgress->setTask($task);
        $userProgress->setUserId('user123');
        $userProgress->setCurrentNode($node);
        $userProgress->setStatus(ProgressStatus::RUNNING);
        $userProgress->setStartTime(new \DateTimeImmutable());

        $this->getRepository()->save($userProgress, true);
        $progressId = $userProgress->getId();

        $foundProgress = $this->getRepository()->find($progressId);
        $this->assertInstanceOf(UserProgress::class, $foundProgress);
        $this->assertSame($progressId, $foundProgress->getId());
        $this->assertSame('user123', $foundProgress->getUserId());
    }

    public function testFindAll(): void
    {
        // Get initial count before we create new data
        $initialProgresses = $this->getRepository()->findAll();
        $initialCount = count($initialProgresses);

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $userProgress1 = new UserProgress();
        $userProgress1->setTask($task);
        $userProgress1->setUserId('user1');
        $userProgress1->setCurrentNode($node);
        $userProgress1->setStatus(ProgressStatus::RUNNING);
        $userProgress1->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress1, true);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::PENDING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress2, true);

        $progresses = $this->getRepository()->findAll();
        $this->assertCount($initialCount + 2, $progresses);
        $this->assertContainsOnlyInstancesOf(UserProgress::class, $progresses);
    }

    public function testFindBy(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $userProgress1 = new UserProgress();
        $userProgress1->setTask($task);
        $userProgress1->setUserId('user1');
        $userProgress1->setCurrentNode($node);
        $userProgress1->setStatus(ProgressStatus::RUNNING);
        $userProgress1->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress1, true);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::FINISHED);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $userProgress2->setFinishTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress2, true);

        $runningProgresses = $this->getRepository()->findBy(['task' => $task, 'status' => ProgressStatus::RUNNING]);
        $this->assertCount(1, $runningProgresses);
        $this->assertSame(ProgressStatus::RUNNING, $runningProgresses[0]->getStatus());

        $finishedProgresses = $this->getRepository()->findBy(['task' => $task, 'status' => ProgressStatus::FINISHED]);
        $this->assertCount(1, $finishedProgresses);
        $this->assertSame(ProgressStatus::FINISHED, $finishedProgresses[0]->getStatus());
    }

    public function testFindByWithOrderAndLimit(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $earlierTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $laterTime = new \DateTimeImmutable('2024-01-01 11:00:00');

        $userProgress1 = new UserProgress();
        $userProgress1->setTask($task);
        $userProgress1->setUserId('user1');
        $userProgress1->setCurrentNode($node);
        $userProgress1->setStatus(ProgressStatus::RUNNING);
        $userProgress1->setStartTime($earlierTime);
        $this->getRepository()->save($userProgress1, true);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime($laterTime);
        $this->getRepository()->save($userProgress2, true);

        $progresses = $this->getRepository()->findBy(
            ['status' => ProgressStatus::RUNNING, 'task' => $task],
            ['startTime' => 'DESC'],
            1,
            0
        );

        $this->assertCount(1, $progresses);
        $this->assertEquals($laterTime, $progresses[0]->getStartTime());
    }

    public function testFindOneBy(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $userProgress = new UserProgress();
        $userProgress->setTask($task);
        $userProgress->setUserId('unique_user');
        $userProgress->setCurrentNode($node);
        $userProgress->setStatus(ProgressStatus::RUNNING);
        $userProgress->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress, true);

        $foundProgress = $this->getRepository()->findOneBy(['userId' => 'unique_user']);
        $this->assertInstanceOf(UserProgress::class, $foundProgress);
        $this->assertSame('unique_user', $foundProgress->getUserId());

        $notFoundProgress = $this->getRepository()->findOneBy(['userId' => 'non_existent']);
        $this->assertNull($notFoundProgress);
    }

    public function testCount(): void
    {
        $initialCount = $this->getRepository()->count([]);

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $userProgress1 = new UserProgress();
        $userProgress1->setTask($task);
        $userProgress1->setUserId('user1');
        $userProgress1->setCurrentNode($node);
        $userProgress1->setStatus(ProgressStatus::RUNNING);
        $userProgress1->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress1, true);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::FINISHED);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $userProgress2->setFinishTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress2, true);

        $totalCount = $this->getRepository()->count([]);
        $this->assertSame($initialCount + 2, $totalCount);

        $runningCount = $this->getRepository()->count(['status' => ProgressStatus::RUNNING, 'task' => $task]);
        $this->assertSame(1, $runningCount);
    }

    public function testFindByTask(): void
    {
        $task1 = new Task();
        $task1->setTitle('Task 1');
        $task1->setDescription('Description 1');
        $task1->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task1->setCrowd($crowd);
        self::getEntityManager()->persist($task1);

        $task2 = new Task();
        $task2->setTitle('Task 2');
        $task2->setDescription('Description 2');
        $task2->setStatus(TaskStatus::RUNNING);
        $crowd2 = new Tag();
        $crowd2->setName('test-tag-' . uniqid());
        $task2->setCrowd($crowd2);
        self::getEntityManager()->persist($task2);

        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('test');
        $resourceConfig1->setAmount(1);

        $node1 = new Node();
        $node1->setName('Node 1');
        $node1->setType(NodeType::START);
        $node1->setTask($task1);
        $node1->setResource($resourceConfig1);
        self::getEntityManager()->persist($node1);

        $resourceConfig2 = new ResourceConfig();
        $resourceConfig2->setType('test');
        $resourceConfig2->setAmount(1);

        $node2 = new Node();
        $node2->setName('Node 2');
        $node2->setType(NodeType::START);
        $node2->setTask($task2);
        $node2->setResource($resourceConfig2);
        self::getEntityManager()->persist($node2);

        $userProgress1 = new UserProgress();
        $userProgress1->setTask($task1);
        $userProgress1->setUserId('user1');
        $userProgress1->setCurrentNode($node1);
        $userProgress1->setStatus(ProgressStatus::RUNNING);
        $userProgress1->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress1, true);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task2);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node2);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress2, true);

        $task1Progresses = $this->getRepository()->findBy(['task' => $task1]);
        $this->assertCount(1, $task1Progresses);
        $this->assertSame($task1, $task1Progresses[0]->getTask());

        $task2Progresses = $this->getRepository()->findBy(['task' => $task2]);
        $this->assertCount(1, $task2Progresses);
        $this->assertSame($task2, $task2Progresses[0]->getTask());
    }

    public function testFindByCurrentNode(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('test');
        $resourceConfig1->setAmount(1);

        $node1 = new Node();
        $node1->setName('Node 1');
        $node1->setType(NodeType::START);
        $node1->setTask($task);
        $node1->setResource($resourceConfig1);
        self::getEntityManager()->persist($node1);

        $resourceConfig2 = new ResourceConfig();
        $resourceConfig2->setType('points');
        $resourceConfig2->setAmount(100);

        $node2 = new Node();
        $node2->setName('Node 2');
        $node2->setType(NodeType::RESOURCE);
        $node2->setTask($task);
        $node2->setResource($resourceConfig2);
        self::getEntityManager()->persist($node2);

        $userProgress1 = new UserProgress();
        $userProgress1->setTask($task);
        $userProgress1->setUserId('user1');
        $userProgress1->setCurrentNode($node1);
        $userProgress1->setStatus(ProgressStatus::RUNNING);
        $userProgress1->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress1, true);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node2);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress2, true);

        $node1Progresses = $this->getRepository()->findBy(['currentNode' => $node1]);
        $this->assertCount(1, $node1Progresses);
        $this->assertSame($node1, $node1Progresses[0]->getCurrentNode());

        $node2Progresses = $this->getRepository()->findBy(['currentNode' => $node2]);
        $this->assertCount(1, $node2Progresses);
        $this->assertSame($node2, $node2Progresses[0]->getCurrentNode());
    }

    public function testFindByStatus(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $userProgress1 = new UserProgress();
        $userProgress1->setTask($task);
        $userProgress1->setUserId('user1');
        $userProgress1->setCurrentNode($node);
        $userProgress1->setStatus(ProgressStatus::PENDING);
        $userProgress1->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress1, true);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::DROPPED);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress2, true);

        $pendingProgresses = $this->getRepository()->findBy(['status' => ProgressStatus::PENDING]);
        $this->assertCount(1, $pendingProgresses);
        $this->assertSame(ProgressStatus::PENDING, $pendingProgresses[0]->getStatus());

        $droppedProgresses = $this->getRepository()->findBy(['status' => ProgressStatus::DROPPED]);
        $this->assertCount(1, $droppedProgresses);
        $this->assertSame(ProgressStatus::DROPPED, $droppedProgresses[0]->getStatus());
    }

    public function testFindByUserId(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $userProgress1 = new UserProgress();
        $userProgress1->setTask($task);
        $userProgress1->setUserId('user123');
        $userProgress1->setCurrentNode($node);
        $userProgress1->setStatus(ProgressStatus::RUNNING);
        $userProgress1->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress1, true);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user456');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress2, true);

        $user123Progresses = $this->getRepository()->findBy(['userId' => 'user123']);
        $this->assertCount(1, $user123Progresses);
        $this->assertSame('user123', $user123Progresses[0]->getUserId());

        $user456Progresses = $this->getRepository()->findBy(['userId' => 'user456']);
        $this->assertCount(1, $user456Progresses);
        $this->assertSame('user456', $user456Progresses[0]->getUserId());
    }

    public function testFindByFinishTime(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $finishTime = new \DateTimeImmutable('2024-01-15 10:00:00');

        $userProgress1 = new UserProgress();
        $userProgress1->setTask($task);
        $userProgress1->setUserId('user1');
        $userProgress1->setCurrentNode($node);
        $userProgress1->setStatus(ProgressStatus::FINISHED);
        $userProgress1->setStartTime(new \DateTimeImmutable());
        $userProgress1->setFinishTime($finishTime);
        $this->getRepository()->save($userProgress1, true);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress2, true);

        $finishedProgresses = $this->getRepository()->findBy(['finishTime' => $finishTime]);
        $this->assertCount(1, $finishedProgresses);
        $this->assertEquals($finishTime, $finishedProgresses[0]->getFinishTime());
    }

    public function testFindByNullFinishTime(): void
    {
        $progresses = $this->getRepository()->findBy(['finishTime' => null]);
        $this->assertIsArray($progresses);
    }

    public function testFindOneByWithMatchingCriteriaShouldRespectOrderByForStartTime(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $earlierTime = new \DateTimeImmutable('2024-01-01 09:00:00');
        $laterTime = new \DateTimeImmutable('2024-01-01 10:00:00');

        $progress1 = new UserProgress();
        $progress1->setTask($task);
        $progress1->setUserId('user1');
        $progress1->setCurrentNode($node);
        $progress1->setStatus(ProgressStatus::RUNNING);
        $progress1->setStartTime($laterTime);
        $this->getRepository()->save($progress1, true);

        $progress2 = new UserProgress();
        $progress2->setTask($task);
        $progress2->setUserId('user2');
        $progress2->setCurrentNode($node);
        $progress2->setStatus(ProgressStatus::RUNNING);
        $progress2->setStartTime($earlierTime);
        $this->getRepository()->save($progress2, true);

        $found = $this->getRepository()->findOneBy(['status' => ProgressStatus::RUNNING, 'task' => $task], ['startTime' => 'ASC']);
        $this->assertInstanceOf(UserProgress::class, $found);
        $this->assertEquals($earlierTime, $found->getStartTime());
    }

    public function testCountByTaskStatus(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        // Create progresses with different statuses
        $progress1 = new UserProgress();
        $progress1->setTask($task);
        $progress1->setUserId('user1');
        $progress1->setCurrentNode($node);
        $progress1->setStatus(ProgressStatus::PENDING);
        $progress1->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($progress1, true);

        $progress2 = new UserProgress();
        $progress2->setTask($task);
        $progress2->setUserId('user2');
        $progress2->setCurrentNode($node);
        $progress2->setStatus(ProgressStatus::RUNNING);
        $progress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($progress2, true);

        $progress3 = new UserProgress();
        $progress3->setTask($task);
        $progress3->setUserId('user3');
        $progress3->setCurrentNode($node);
        $progress3->setStatus(ProgressStatus::FINISHED);
        $progress3->setStartTime(new \DateTimeImmutable());
        $progress3->setFinishTime(new \DateTimeImmutable());
        $this->getRepository()->save($progress3, true);

        $progress4 = new UserProgress();
        $progress4->setTask($task);
        $progress4->setUserId('user4');
        $progress4->setCurrentNode($node);
        $progress4->setStatus(ProgressStatus::DROPPED);
        $progress4->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($progress4, true);

        $counts = $this->getRepository()->countByTaskStatus($task);

        $this->assertIsArray($counts);
        $this->assertArrayHasKey('pending', $counts);
        $this->assertArrayHasKey('running', $counts);
        $this->assertArrayHasKey('finished', $counts);
        $this->assertArrayHasKey('dropped', $counts);
        $this->assertEquals(1, $counts['pending']);
        $this->assertEquals(1, $counts['running']);
        $this->assertEquals(1, $counts['finished']);
        $this->assertEquals(1, $counts['dropped']);
    }

    public function testFindRunningByTask(): void
    {
        $task1 = new Task();
        $task1->setTitle('Task 1');
        $task1->setDescription('Description 1');
        $task1->setStatus(TaskStatus::DRAFT);
        $crowd1 = new Tag();
        $crowd1->setName('test-tag-' . uniqid());
        $task1->setCrowd($crowd1);
        self::getEntityManager()->persist($task1);

        $task2 = new Task();
        $task2->setTitle('Task 2');
        $task2->setDescription('Description 2');
        $task2->setStatus(TaskStatus::RUNNING);
        $crowd2 = new Tag();
        $crowd2->setName('test-tag-' . uniqid());
        $task2->setCrowd($crowd2);
        self::getEntityManager()->persist($task2);

        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('test');
        $resourceConfig1->setAmount(1);

        $node1 = new Node();
        $node1->setName('Node 1');
        $node1->setType(NodeType::START);
        $node1->setTask($task1);
        $node1->setResource($resourceConfig1);
        self::getEntityManager()->persist($node1);

        $resourceConfig2 = new ResourceConfig();
        $resourceConfig2->setType('test');
        $resourceConfig2->setAmount(1);

        $node2 = new Node();
        $node2->setName('Node 2');
        $node2->setType(NodeType::START);
        $node2->setTask($task2);
        $node2->setResource($resourceConfig2);
        self::getEntityManager()->persist($node2);

        // Running progress for task1
        $progress1 = new UserProgress();
        $progress1->setTask($task1);
        $progress1->setUserId('user1');
        $progress1->setCurrentNode($node1);
        $progress1->setStatus(ProgressStatus::RUNNING);
        $progress1->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($progress1, true);

        // Finished progress for task1
        $progress2 = new UserProgress();
        $progress2->setTask($task1);
        $progress2->setUserId('user2');
        $progress2->setCurrentNode($node1);
        $progress2->setStatus(ProgressStatus::FINISHED);
        $progress2->setStartTime(new \DateTimeImmutable());
        $progress2->setFinishTime(new \DateTimeImmutable());
        $this->getRepository()->save($progress2, true);

        // Running progress for task2
        $progress3 = new UserProgress();
        $progress3->setTask($task2);
        $progress3->setUserId('user3');
        $progress3->setCurrentNode($node2);
        $progress3->setStatus(ProgressStatus::RUNNING);
        $progress3->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($progress3, true);

        $results = $this->getRepository()->findRunningByTask($task1);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(UserProgress::class, $results[0]);
        $this->assertSame($task1, $results[0]->getTask());
        $this->assertEquals(ProgressStatus::RUNNING, $results[0]->getStatus());
        $this->assertEquals('user1', $results[0]->getUserId());
    }

    public function testFindByAssociationCurrentNode(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('test');
        $resourceConfig1->setAmount(1);

        $node1 = new Node();
        $node1->setName('Node 1');
        $node1->setType(NodeType::START);
        $node1->setTask($task);
        $node1->setResource($resourceConfig1);
        self::getEntityManager()->persist($node1);

        $resourceConfig2 = new ResourceConfig();
        $resourceConfig2->setType('points');
        $resourceConfig2->setAmount(100);

        $node2 = new Node();
        $node2->setName('Node 2');
        $node2->setType(NodeType::RESOURCE);
        $node2->setTask($task);
        $node2->setResource($resourceConfig2);
        self::getEntityManager()->persist($node2);

        // Create progresses for different nodes
        for ($i = 1; $i <= 3; ++$i) {
            $progress = new UserProgress();
            $progress->setTask($task);
            $progress->setUserId('node1_user' . $i);
            $progress->setCurrentNode($node1);
            $progress->setStatus(ProgressStatus::RUNNING);
            $progress->setStartTime(new \DateTimeImmutable());
            $this->getRepository()->save($progress, true);
        }

        $progress2 = new UserProgress();
        $progress2->setTask($task);
        $progress2->setUserId('node2_user');
        $progress2->setCurrentNode($node2);
        $progress2->setStatus(ProgressStatus::RUNNING);
        $progress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($progress2, true);

        $results = $this->getRepository()->findBy(['currentNode' => $node1]);
        $this->assertCount(3, $results);

        foreach ($results as $result) {
            $this->assertInstanceOf(UserProgress::class, $result);
            $this->assertSame($node1, $result->getCurrentNode());
        }
    }

    public function testCountByAssociationCurrentNode(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('test');
        $resourceConfig1->setAmount(1);

        $node1 = new Node();
        $node1->setName('Node 1');
        $node1->setType(NodeType::START);
        $node1->setTask($task);
        $node1->setResource($resourceConfig1);
        self::getEntityManager()->persist($node1);

        $resourceConfig2 = new ResourceConfig();
        $resourceConfig2->setType('points');
        $resourceConfig2->setAmount(100);

        $node2 = new Node();
        $node2->setName('Node 2');
        $node2->setType(NodeType::RESOURCE);
        $node2->setTask($task);
        $node2->setResource($resourceConfig2);
        self::getEntityManager()->persist($node2);

        // Create 4 progresses for node1
        for ($i = 1; $i <= 4; ++$i) {
            $progress = new UserProgress();
            $progress->setTask($task);
            $progress->setUserId('node1_user' . $i);
            $progress->setCurrentNode($node1);
            $progress->setStatus(ProgressStatus::RUNNING);
            $progress->setStartTime(new \DateTimeImmutable());
            $this->getRepository()->save($progress, true);
        }

        // Create 2 progresses for node2
        for ($i = 1; $i <= 2; ++$i) {
            $progress = new UserProgress();
            $progress->setTask($task);
            $progress->setUserId('node2_user' . $i);
            $progress->setCurrentNode($node2);
            $progress->setStatus(ProgressStatus::RUNNING);
            $progress->setStartTime(new \DateTimeImmutable());
            $this->getRepository()->save($progress, true);
        }

        $count = $this->getRepository()->count(['currentNode' => $node1]);
        $this->assertEquals(4, $count);
    }

    public function testFindByNullableFieldFinishTime(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $progress1 = new UserProgress();
        $progress1->setTask($task);
        $progress1->setUserId('user1');
        $progress1->setCurrentNode($node);
        $progress1->setStatus(ProgressStatus::FINISHED);
        $progress1->setStartTime(new \DateTimeImmutable());
        $progress1->setFinishTime(new \DateTimeImmutable());
        $this->getRepository()->save($progress1, true);

        $progress2 = new UserProgress();
        $progress2->setTask($task);
        $progress2->setUserId('user2');
        $progress2->setCurrentNode($node);
        $progress2->setStatus(ProgressStatus::RUNNING);
        $progress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($progress2, true);

        $results = $this->getRepository()->findBy(['finishTime' => null]);
        $this->assertGreaterThanOrEqual(1, count($results));

        foreach ($results as $result) {
            $this->assertNull($result->getFinishTime());
        }
    }

    public function testCountByNullableFieldFinishTime(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $progress1 = new UserProgress();
        $progress1->setTask($task);
        $progress1->setUserId('user1');
        $progress1->setCurrentNode($node);
        $progress1->setStatus(ProgressStatus::FINISHED);
        $progress1->setStartTime(new \DateTimeImmutable());
        $progress1->setFinishTime(new \DateTimeImmutable());
        $this->getRepository()->save($progress1, true);

        $progress2 = new UserProgress();
        $progress2->setTask($task);
        $progress2->setUserId('user2');
        $progress2->setCurrentNode($node);
        $progress2->setStatus(ProgressStatus::RUNNING);
        $progress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($progress2, true);

        $count = $this->getRepository()->count(['finishTime' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindReadyForNextNode(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $userProgress1 = new UserProgress();
        $userProgress1->setTask($task);
        $userProgress1->setUserId('user1');
        $userProgress1->setCurrentNode($node);
        $userProgress1->setStatus(ProgressStatus::RUNNING);
        $userProgress1->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress1, true);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress2, true);

        // Create node stages with activated status
        $stageRepository = self::getService(NodeStageRepository::class);

        $readyStage = new NodeStage();
        $readyStage->setNode($node);
        $readyStage->setUserProgress($userProgress1);
        $readyStage->setStatus(NodeStageStatus::RUNNING);
        $readyStage->setReachTime(new \DateTimeImmutable());
        $readyStage->setActiveTime(new \DateTimeImmutable());
        $stageRepository->save($readyStage, true);

        $notReadyStage = new NodeStage();
        $notReadyStage->setNode($node);
        $notReadyStage->setUserProgress($userProgress2);
        $notReadyStage->setStatus(NodeStageStatus::PENDING);
        $notReadyStage->setReachTime(new \DateTimeImmutable());
        $stageRepository->save($notReadyStage, true);

        $results = $this->getRepository()->findReadyForNextNode($node);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(UserProgress::class, $results[0]);
        $this->assertSame($userProgress1->getId(), $results[0]->getId());
        $this->assertEquals(ProgressStatus::RUNNING, $results[0]->getStatus());
    }

    public function testFindShouldDropped(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $userProgress1 = new UserProgress();
        $userProgress1->setTask($task);
        $userProgress1->setUserId('user1');
        $userProgress1->setCurrentNode($node);
        $userProgress1->setStatus(ProgressStatus::RUNNING);
        $userProgress1->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress1, true);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress2, true);

        $oldTouchTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $recentTouchTime = new \DateTimeImmutable('2024-01-02 10:00:00');
        $beforeTime = new \DateTimeImmutable('2024-01-01 12:00:00');

        // Create node stages
        $stageRepository = self::getService(NodeStageRepository::class);

        // Should be dropped (touched but not activated, old touch time)
        $shouldDropStage = new NodeStage();
        $shouldDropStage->setNode($node);
        $shouldDropStage->setUserProgress($userProgress1);
        $shouldDropStage->setStatus(NodeStageStatus::PENDING);
        $shouldDropStage->setReachTime(new \DateTimeImmutable());
        $shouldDropStage->setTouchTime($oldTouchTime);
        $stageRepository->save($shouldDropStage, true);

        // Should not be dropped (recent touch time)
        $shouldNotDropStage = new NodeStage();
        $shouldNotDropStage->setNode($node);
        $shouldNotDropStage->setUserProgress($userProgress2);
        $shouldNotDropStage->setStatus(NodeStageStatus::PENDING);
        $shouldNotDropStage->setReachTime(new \DateTimeImmutable());
        $shouldNotDropStage->setTouchTime($recentTouchTime);
        $stageRepository->save($shouldNotDropStage, true);

        $results = $this->getRepository()->findShouldDropped($node, $beforeTime);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(UserProgress::class, $results[0]);
        $this->assertSame($userProgress1->getId(), $results[0]->getId());
        $this->assertEquals(ProgressStatus::RUNNING, $results[0]->getStatus());
    }

    public function testCountUserProgressWithStages(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $userProgress1 = new UserProgress();
        $userProgress1->setTask($task);
        $userProgress1->setUserId('user1');
        $userProgress1->setCurrentNode($node);
        $userProgress1->setStatus(ProgressStatus::RUNNING);
        $userProgress1->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress1, true);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress2, true);

        // Add stages to userProgress1
        $stageRepository = self::getService(NodeStageRepository::class);
        $stage1 = new NodeStage();
        $stage1->setNode($node);
        $stage1->setUserProgress($userProgress1);
        $stage1->setStatus(NodeStageStatus::PENDING);
        $stage1->setReachTime(new \DateTimeImmutable());
        $stageRepository->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setNode($node);
        $stage2->setUserProgress($userProgress1);
        $stage2->setStatus(NodeStageStatus::RUNNING);
        $stage2->setReachTime(new \DateTimeImmutable());
        $stage2->setTouchTime(new \DateTimeImmutable());
        $stageRepository->save($stage2, true);

        // Add only one stage to userProgress2
        $stage3 = new NodeStage();
        $stage3->setNode($node);
        $stage3->setUserProgress($userProgress2);
        $stage3->setStatus(NodeStageStatus::PENDING);
        $stage3->setReachTime(new \DateTimeImmutable());
        $stageRepository->save($stage3, true);

        // 通过拥有方 (NodeStage) 进行查询来验证关联关系
        $stagesCount = $stageRepository->count(['userProgress' => $userProgress1]);
        $this->assertEquals(2, $stagesCount);

        $stagesCount2 = $stageRepository->count(['userProgress' => $userProgress2]);
        $this->assertEquals(1, $stagesCount2);
    }

    public function testFindUserProgressWithStages(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $userProgress1 = new UserProgress();
        $userProgress1->setTask($task);
        $userProgress1->setUserId('user1');
        $userProgress1->setCurrentNode($node);
        $userProgress1->setStatus(ProgressStatus::RUNNING);
        $userProgress1->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress1, true);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($userProgress2, true);

        // Add stages to userProgress1
        $stageRepository = self::getService(NodeStageRepository::class);
        $stage1 = new NodeStage();
        $stage1->setNode($node);
        $stage1->setUserProgress($userProgress1);
        $stage1->setStatus(NodeStageStatus::PENDING);
        $stage1->setReachTime(new \DateTimeImmutable());
        $stageRepository->save($stage1, true);

        // 通过拥有方 (NodeStage) 进行查询来验证关联关系
        $stages = $stageRepository->findBy(['userProgress' => $userProgress1]);
        $this->assertIsArray($stages);
        $this->assertCount(1, $stages);
        $this->assertEquals($userProgress1->getId(), $stages[0]->getUserProgress()->getId());
    }

    public function testFindOneByShouldRespectOrderBy(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $progress1 = new UserProgress();
        $progress1->setTask($task);
        $progress1->setUserId('user_z');
        $progress1->setCurrentNode($node);
        $progress1->setStatus(ProgressStatus::RUNNING);
        $progress1->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($progress1, true);

        $progress2 = new UserProgress();
        $progress2->setTask($task);
        $progress2->setUserId('user_a');
        $progress2->setCurrentNode($node);
        $progress2->setStatus(ProgressStatus::RUNNING);
        $progress2->setStartTime(new \DateTimeImmutable());
        $this->getRepository()->save($progress2, true);

        $found = $this->getRepository()->findOneBy(
            ['status' => ProgressStatus::RUNNING, 'task' => $task],
            ['userId' => 'ASC']
        );

        $this->assertInstanceOf(UserProgress::class, $found);
        $this->assertEquals('user_a', $found->getUserId());
    }

    protected function createNewEntity(): object
    {
        $task = new Task();
        $task->setTitle('Test Task ' . uniqid());
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $task->setStartTime(new \DateTimeImmutable());
        $task->setEndTime(new \DateTimeImmutable('+7 days'));
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node ' . uniqid());
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $entity = new UserProgress();
        $entity->setUserId('test-user-' . uniqid());
        $entity->setStatus(ProgressStatus::RUNNING);
        $entity->setTask($task);
        $entity->setCurrentNode($node);
        $entity->setStartTime(new \DateTimeImmutable());

        return $entity;
    }
}
