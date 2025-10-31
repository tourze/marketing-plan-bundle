<?php

namespace MarketingPlanBundle\Tests\Repository;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeStage;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Entity\UserProgress;
use MarketingPlanBundle\Enum\DropReason;
use MarketingPlanBundle\Enum\NodeStageStatus;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\ProgressStatus;
use MarketingPlanBundle\Enum\TaskStatus;
use MarketingPlanBundle\Repository\NodeStageRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;

/**
 * @internal
 */
#[CoversClass(NodeStageRepository::class)]
#[RunTestsInSeparateProcesses]
final class NodeStageRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 可以在这里添加测试初始化逻辑
    }

    protected function getRepository(): NodeStageRepository
    {
        return self::getService(NodeStageRepository::class);
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
        $userProgress->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress);

        $stage = new NodeStage();
        $stage->setUserProgress($userProgress);
        $stage->setNode($node);
        $stage->setStatus(NodeStageStatus::PENDING);
        $stage->setReachTime(new \DateTimeImmutable());

        $this->getRepository()->save($stage, true);

        $this->assertGreaterThan(0, $stage->getId());
        $this->assertSame($userProgress, $stage->getUserProgress());
        $this->assertSame($node, $stage->getNode());
        $this->assertSame(NodeStageStatus::PENDING, $stage->getStatus());
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
        $userProgress->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress);

        $stage = new NodeStage();
        $stage->setUserProgress($userProgress);
        $stage->setNode($node);
        $stage->setStatus(NodeStageStatus::PENDING);
        $stage->setReachTime(new \DateTimeImmutable());

        $this->getRepository()->save($stage, true);
        $stageId = $stage->getId();

        $this->getRepository()->remove($stage, true);

        $removedStage = $this->getRepository()->find($stageId);
        $this->assertNull($removedStage);
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
        $userProgress->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress);

        $stage = new NodeStage();
        $stage->setUserProgress($userProgress);
        $stage->setNode($node);
        $stage->setStatus(NodeStageStatus::PENDING);
        $stage->setReachTime(new \DateTimeImmutable());

        $this->getRepository()->save($stage, true);
        $stageId = $stage->getId();

        $foundStage = $this->getRepository()->find($stageId);
        $this->assertInstanceOf(NodeStage::class, $foundStage);
        $this->assertSame($stageId, $foundStage->getId());
        $this->assertSame(NodeStageStatus::PENDING, $foundStage->getStatus());
    }

    public function testFindAll(): void
    {
        // Get initial count before we create new data
        $initialStages = $this->getRepository()->findAll();
        $initialCount = count($initialStages);

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
        $userProgress1->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress1);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $userProgress2->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress2);

        $stage1 = new NodeStage();
        $stage1->setUserProgress($userProgress1);
        $stage1->setNode($node);
        $stage1->setStatus(NodeStageStatus::PENDING);
        $stage1->setReachTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setUserProgress($userProgress2);
        $stage2->setNode($node);
        $stage2->setStatus(NodeStageStatus::RUNNING);
        $stage2->setReachTime(new \DateTimeImmutable());
        $stage2->setActiveTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage2, true);

        $stages = $this->getRepository()->findAll();

        // Assert we have the expected number of total stages (initial + our 2 new ones)
        $this->assertCount($initialCount + 2, $stages);
        $this->assertContainsOnlyInstancesOf(NodeStage::class, $stages);

        // Verify our newly created stages are included
        $stageIds = array_map(fn (NodeStage $stage) => $stage->getId(), $stages);
        $this->assertContains($stage1->getId(), $stageIds);
        $this->assertContains($stage2->getId(), $stageIds);
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
        $userProgress1->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress1);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $userProgress2->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress2);

        $stage1 = new NodeStage();
        $stage1->setUserProgress($userProgress1);
        $stage1->setNode($node);
        $stage1->setStatus(NodeStageStatus::PENDING);
        $stage1->setReachTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setUserProgress($userProgress2);
        $stage2->setNode($node);
        $stage2->setStatus(NodeStageStatus::RUNNING);
        $stage2->setReachTime(new \DateTimeImmutable());
        $stage2->setActiveTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage2, true);

        // Query by node to avoid interference from other test data
        $pendingStages = $this->getRepository()->findBy(['node' => $node, 'status' => NodeStageStatus::PENDING]);
        $this->assertCount(1, $pendingStages);
        $this->assertSame(NodeStageStatus::PENDING, $pendingStages[0]->getStatus());
        $this->assertSame($stage1->getId(), $pendingStages[0]->getId());

        $activeStages = $this->getRepository()->findBy(['node' => $node, 'status' => NodeStageStatus::RUNNING]);
        $this->assertCount(1, $activeStages);
        $this->assertSame(NodeStageStatus::RUNNING, $activeStages[0]->getStatus());
        $this->assertSame($stage2->getId(), $activeStages[0]->getId());
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

        $userProgress1 = new UserProgress();
        $userProgress1->setTask($task);
        $userProgress1->setUserId('user1');
        $userProgress1->setCurrentNode($node);
        $userProgress1->setStatus(ProgressStatus::RUNNING);
        $userProgress1->setStartTime(new \DateTimeImmutable());
        $userProgress1->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress1);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $userProgress2->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress2);

        $earlierTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $laterTime = new \DateTimeImmutable('2024-01-01 11:00:00');

        $stage1 = new NodeStage();
        $stage1->setUserProgress($userProgress1);
        $stage1->setNode($node);
        $stage1->setStatus(NodeStageStatus::PENDING);
        $stage1->setReachTime($earlierTime);
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setUserProgress($userProgress2);
        $stage2->setNode($node);
        $stage2->setStatus(NodeStageStatus::PENDING);
        $stage2->setReachTime($laterTime);
        $this->getRepository()->save($stage2, true);

        $stages = $this->getRepository()->findBy(
            ['status' => NodeStageStatus::PENDING],
            ['reachTime' => 'DESC'],
            1,
            0
        );

        $this->assertCount(1, $stages);
        $this->assertEquals($laterTime, $stages[0]->getReachTime());
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
        $userProgress->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress);

        $stage = new NodeStage();
        $stage->setUserProgress($userProgress);
        $stage->setNode($node);
        $stage->setStatus(NodeStageStatus::PENDING);
        $stage->setReachTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage, true);

        $foundStage = $this->getRepository()->findOneBy(['userProgress' => $userProgress]);
        $this->assertInstanceOf(NodeStage::class, $foundStage);
        $this->assertSame($userProgress, $foundStage->getUserProgress());

        $notFoundStage = $this->getRepository()->findOneBy(['status' => NodeStageStatus::FINISHED]);
        $this->assertNull($notFoundStage);
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
        $userProgress1->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress1);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $userProgress2->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress2);

        $stage1 = new NodeStage();
        $stage1->setUserProgress($userProgress1);
        $stage1->setNode($node);
        $stage1->setStatus(NodeStageStatus::PENDING);
        $stage1->setReachTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setUserProgress($userProgress2);
        $stage2->setNode($node);
        $stage2->setStatus(NodeStageStatus::RUNNING);
        $stage2->setReachTime(new \DateTimeImmutable());
        $stage2->setActiveTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage2, true);

        $totalCount = $this->getRepository()->count([]);
        $this->assertSame($initialCount + 2, $totalCount);

        $pendingCount = $this->getRepository()->count(['status' => NodeStageStatus::PENDING]);
        $this->assertSame(1, $pendingCount);
    }

    public function testFindByUserProgress(): void
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
        $userProgress1->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress1);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $userProgress2->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress2);

        $stage1 = new NodeStage();
        $stage1->setUserProgress($userProgress1);
        $stage1->setNode($node);
        $stage1->setStatus(NodeStageStatus::PENDING);
        $stage1->setReachTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setUserProgress($userProgress2);
        $stage2->setNode($node);
        $stage2->setStatus(NodeStageStatus::RUNNING);
        $stage2->setReachTime(new \DateTimeImmutable());
        $stage2->setActiveTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage2, true);

        $user1Stages = $this->getRepository()->findBy(['userProgress' => $userProgress1]);
        $this->assertCount(1, $user1Stages);
        $this->assertSame($userProgress1, $user1Stages[0]->getUserProgress());

        $user2Stages = $this->getRepository()->findBy(['userProgress' => $userProgress2]);
        $this->assertCount(1, $user2Stages);
        $this->assertSame($userProgress2, $user2Stages[0]->getUserProgress());
    }

    public function testFindByNode(): void
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
        $resourceConfig2->setType('test');
        $resourceConfig2->setAmount(1);

        $node2 = new Node();
        $node2->setName('Node 2');
        $node2->setType(NodeType::RESOURCE);
        $node2->setTask($task);
        $node2->setResource($resourceConfig2);
        self::getEntityManager()->persist($node2);

        $userProgress = new UserProgress();
        $userProgress->setTask($task);
        $userProgress->setUserId('user123');
        $userProgress->setCurrentNode($node1);
        $userProgress->setStatus(ProgressStatus::RUNNING);
        $userProgress->setStartTime(new \DateTimeImmutable());
        $userProgress->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress);

        $stage1 = new NodeStage();
        $stage1->setUserProgress($userProgress);
        $stage1->setNode($node1);
        $stage1->setStatus(NodeStageStatus::PENDING);
        $stage1->setReachTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setUserProgress($userProgress);
        $stage2->setNode($node2);
        $stage2->setStatus(NodeStageStatus::RUNNING);
        $stage2->setReachTime(new \DateTimeImmutable());
        $stage2->setActiveTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage2, true);

        $node1Stages = $this->getRepository()->findBy(['node' => $node1]);
        $this->assertCount(1, $node1Stages);
        $this->assertSame($node1, $node1Stages[0]->getNode());

        $node2Stages = $this->getRepository()->findBy(['node' => $node2]);
        $this->assertCount(1, $node2Stages);
        $this->assertSame($node2, $node2Stages[0]->getNode());
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
        $userProgress1->setStatus(ProgressStatus::RUNNING);
        $userProgress1->setStartTime(new \DateTimeImmutable());
        $userProgress1->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress1);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $userProgress2->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress2);

        $stage1 = new NodeStage();
        $stage1->setUserProgress($userProgress1);
        $stage1->setNode($node);
        $stage1->setStatus(NodeStageStatus::PENDING);
        $stage1->setReachTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setUserProgress($userProgress2);
        $stage2->setNode($node);
        $stage2->setStatus(NodeStageStatus::RUNNING);
        $stage2->setReachTime(new \DateTimeImmutable());
        $stage2->setTouchTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage2, true);

        // Query by node to avoid interference from other test data
        $pendingStages = $this->getRepository()->findBy(['node' => $node, 'status' => NodeStageStatus::PENDING]);
        $this->assertCount(1, $pendingStages);
        $this->assertSame(NodeStageStatus::PENDING, $pendingStages[0]->getStatus());
        $this->assertSame($stage1->getId(), $pendingStages[0]->getId());

        $touchedStages = $this->getRepository()->findBy(['node' => $node, 'status' => NodeStageStatus::RUNNING]);
        $this->assertCount(1, $touchedStages);
        $this->assertSame(NodeStageStatus::RUNNING, $touchedStages[0]->getStatus());
        $this->assertSame($stage2->getId(), $touchedStages[0]->getId());
    }

    public function testFindByNullTouchTime(): void
    {
        $stages = $this->getRepository()->findBy(['touchTime' => null]);
        $this->assertIsArray($stages);
    }

    public function testFindByNullDropTime(): void
    {
        $stages = $this->getRepository()->findBy(['dropTime' => null]);
        $this->assertIsArray($stages);
    }

    public function testFindOneByWithMatchingCriteriaShouldRespectOrderBy(): void
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
        self::getEntityManager()->persist($userProgress);

        $stage1 = new NodeStage();
        $stage1->setNode($node);
        $stage1->setUserProgress($userProgress);
        $stage1->setStatus(NodeStageStatus::FINISHED);
        $stage1->setReachTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setNode($node);
        $stage2->setUserProgress($userProgress);
        $stage2->setStatus(NodeStageStatus::FINISHED);
        $stage2->setReachTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage2, true);

        $found = $this->getRepository()->findOneBy(['status' => NodeStageStatus::FINISHED], ['reachTime' => 'DESC']);
        $this->assertInstanceOf(NodeStage::class, $found);
        $this->assertNotNull($found->getReachTime());
    }

    public function testCountByAssociationNodeShouldReturnCorrectNumber(): void
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
        $resourceConfig2->setType('test');
        $resourceConfig2->setAmount(1);

        $node2 = new Node();
        $node2->setName('Node 2');
        $node2->setType(NodeType::RESOURCE);
        $node2->setTask($task);
        $node2->setResource($resourceConfig2);
        self::getEntityManager()->persist($node2);

        $userProgress = new UserProgress();
        $userProgress->setTask($task);
        $userProgress->setUserId('user123');
        $userProgress->setCurrentNode($node1);
        $userProgress->setStatus(ProgressStatus::RUNNING);
        $userProgress->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress);

        for ($i = 1; $i <= 4; ++$i) {
            $stage = new NodeStage();
            $stage->setNode($node1);
            $stage->setUserProgress($userProgress);
            $stage->setStatus(NodeStageStatus::FINISHED);
            $stage->setReachTime(new \DateTimeImmutable());
            $this->getRepository()->save($stage, true);
        }

        for ($i = 1; $i <= 2; ++$i) {
            $stage = new NodeStage();
            $stage->setNode($node2);
            $stage->setUserProgress($userProgress);
            $stage->setStatus(NodeStageStatus::FINISHED);
            $stage->setReachTime(new \DateTimeImmutable());
            $this->getRepository()->save($stage, true);
        }

        $count = $this->getRepository()->count(['node' => $node1]);
        $this->assertSame(4, $count);
    }

    public function testCountByAssociationUserProgressShouldReturnCorrectNumber(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);

        $catalogType = new CatalogType();
        $catalogType->setName('test-type');
        $catalogType->setCode('test_type');
        self::getEntityManager()->persist($catalogType);

        $catalog = new Catalog();
        $catalog->setName('Test Category');
        $catalog->setType($catalogType);
        $catalog->setEnabled(true);
        self::getEntityManager()->persist($catalog);

        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $crowd->setType(TagType::StaticTag);
        $crowd->setCatalog($catalog);
        $crowd->setValid(true);
        self::getEntityManager()->persist($crowd);
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
        $userProgress1->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress1);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $userProgress2->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress2);

        for ($i = 1; $i <= 3; ++$i) {
            $stage = new NodeStage();
            $stage->setNode($node);
            $stage->setUserProgress($userProgress1);
            $stage->setStatus(NodeStageStatus::FINISHED);
            $stage->setReachTime(new \DateTimeImmutable());
            $this->getRepository()->save($stage, true);
        }

        for ($i = 1; $i <= 2; ++$i) {
            $stage = new NodeStage();
            $stage->setNode($node);
            $stage->setUserProgress($userProgress2);
            $stage->setStatus(NodeStageStatus::FINISHED);
            $stage->setReachTime(new \DateTimeImmutable());
            $this->getRepository()->save($stage, true);
        }

        $count = $this->getRepository()->count(['userProgress' => $userProgress1]);
        $this->assertSame(3, $count);
    }

    public function testFindOneByWithMatchingCriteriaShouldRespectOrderByForReachTime(): void
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
        $userProgress->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress);

        $earlierTime = new \DateTimeImmutable('2024-01-01 09:00:00');
        $laterTime = new \DateTimeImmutable('2024-01-01 10:00:00');

        $stage1 = new NodeStage();
        $stage1->setNode($node);
        $stage1->setUserProgress($userProgress);
        $stage1->setStatus(NodeStageStatus::PENDING);
        $stage1->setReachTime($laterTime);
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setNode($node);
        $stage2->setUserProgress($userProgress);
        $stage2->setStatus(NodeStageStatus::PENDING);
        $stage2->setReachTime($earlierTime);
        $this->getRepository()->save($stage2, true);

        $found = $this->getRepository()->findOneBy(['status' => NodeStageStatus::PENDING], ['reachTime' => 'ASC']);
        $this->assertInstanceOf(NodeStage::class, $found);
        $this->assertEquals($earlierTime, $found->getReachTime());
    }

    public function testCountNodeStats(): void
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
        $userProgress1->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress1);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $userProgress2->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress2);

        // Create stage with touch and active time
        $stage1 = new NodeStage();
        $stage1->setNode($node);
        $stage1->setUserProgress($userProgress1);
        $stage1->setStatus(NodeStageStatus::RUNNING);
        $stage1->setReachTime(new \DateTimeImmutable());
        $stage1->setTouchTime(new \DateTimeImmutable());
        $stage1->setActiveTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage1, true);

        // Create stage with only touch time
        $stage2 = new NodeStage();
        $stage2->setNode($node);
        $stage2->setUserProgress($userProgress2);
        $stage2->setStatus(NodeStageStatus::PENDING);
        $stage2->setReachTime(new \DateTimeImmutable());
        $stage2->setTouchTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage2, true);

        $stats = $this->getRepository()->countNodeStats($node);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('touched', $stats);
        $this->assertArrayHasKey('activated', $stats);
        $this->assertArrayHasKey('dropped', $stats);
        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(2, $stats['touched']);
        $this->assertEquals(1, $stats['activated']);
        $this->assertEquals(0, $stats['dropped']);
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
        $userProgress1->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress1);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $userProgress2->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress2);

        // Ready stage (activated and running)
        $readyStage = new NodeStage();
        $readyStage->setNode($node);
        $readyStage->setUserProgress($userProgress1);
        $readyStage->setStatus(NodeStageStatus::RUNNING);
        $readyStage->setReachTime(new \DateTimeImmutable());
        $readyStage->setActiveTime(new \DateTimeImmutable());
        $this->getRepository()->save($readyStage, true);

        // Not ready stage (not activated)
        $notReadyStage = new NodeStage();
        $notReadyStage->setNode($node);
        $notReadyStage->setUserProgress($userProgress2);
        $notReadyStage->setStatus(NodeStageStatus::PENDING);
        $notReadyStage->setReachTime(new \DateTimeImmutable());
        $this->getRepository()->save($notReadyStage, true);

        $results = $this->getRepository()->findReadyForNextNode($node);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(NodeStage::class, $results[0]);
        $this->assertSame($readyStage->getId(), $results[0]->getId());
        $this->assertNotNull($results[0]->getActiveTime());
        $this->assertNull($results[0]->getDropTime());
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
        $userProgress1->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress1);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $userProgress2->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress2);

        $oldTouchTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $recentTouchTime = new \DateTimeImmutable('2024-01-02 10:00:00');
        $beforeTime = new \DateTimeImmutable('2024-01-01 12:00:00');

        // Should be dropped (touched but not activated, old touch time)
        $shouldDropStage = new NodeStage();
        $shouldDropStage->setNode($node);
        $shouldDropStage->setUserProgress($userProgress1);
        $shouldDropStage->setStatus(NodeStageStatus::PENDING);
        $shouldDropStage->setReachTime(new \DateTimeImmutable());
        $shouldDropStage->setTouchTime($oldTouchTime);
        $this->getRepository()->save($shouldDropStage, true);

        // Should not be dropped (recent touch time)
        $shouldNotDropStage = new NodeStage();
        $shouldNotDropStage->setNode($node);
        $shouldNotDropStage->setUserProgress($userProgress2);
        $shouldNotDropStage->setStatus(NodeStageStatus::PENDING);
        $shouldNotDropStage->setReachTime(new \DateTimeImmutable());
        $shouldNotDropStage->setTouchTime($recentTouchTime);
        $this->getRepository()->save($shouldNotDropStage, true);

        $results = $this->getRepository()->findShouldDropped($node, $beforeTime);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(NodeStage::class, $results[0]);
        $this->assertSame($shouldDropStage->getId(), $results[0]->getId());
        $this->assertNotNull($results[0]->getTouchTime());
        $this->assertNull($results[0]->getActiveTime());
        $this->assertNull($results[0]->getDropTime());
        $this->assertLessThan($beforeTime, $results[0]->getTouchTime());
    }

    public function testFindByNullableFieldActiveTime(): void
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
        $userProgress->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress);

        $stage1 = new NodeStage();
        $stage1->setNode($node);
        $stage1->setUserProgress($userProgress);
        $stage1->setStatus(NodeStageStatus::RUNNING);
        $stage1->setReachTime(new \DateTimeImmutable());
        $stage1->setActiveTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setNode($node);
        $stage2->setUserProgress($userProgress);
        $stage2->setStatus(NodeStageStatus::PENDING);
        $stage2->setReachTime(new \DateTimeImmutable());
        $stage2->setActiveTime(null);
        $this->getRepository()->save($stage2, true);

        $results = $this->getRepository()->findBy(['activeTime' => null]);
        $this->assertGreaterThanOrEqual(1, count($results));

        foreach ($results as $result) {
            $this->assertNull($result->getActiveTime());
        }
    }

    public function testCountByNullableFieldActiveTime(): void
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
        $userProgress->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress);

        $stage1 = new NodeStage();
        $stage1->setNode($node);
        $stage1->setUserProgress($userProgress);
        $stage1->setStatus(NodeStageStatus::RUNNING);
        $stage1->setReachTime(new \DateTimeImmutable());
        $stage1->setActiveTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setNode($node);
        $stage2->setUserProgress($userProgress);
        $stage2->setStatus(NodeStageStatus::PENDING);
        $stage2->setReachTime(new \DateTimeImmutable());
        $stage2->setActiveTime(null);
        $this->getRepository()->save($stage2, true);

        $count = $this->getRepository()->count(['activeTime' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByAssociationDropReason(): void
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
        $userProgress->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress);

        $stage1 = new NodeStage();
        $stage1->setNode($node);
        $stage1->setUserProgress($userProgress);
        $stage1->setStatus(NodeStageStatus::DROPPED);
        $stage1->setReachTime(new \DateTimeImmutable());
        $stage1->setDropTime(new \DateTimeImmutable());
        $stage1->setDropReason(DropReason::TIMEOUT);
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setNode($node);
        $stage2->setUserProgress($userProgress);
        $stage2->setStatus(NodeStageStatus::RUNNING);
        $stage2->setReachTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage2, true);

        $results = $this->getRepository()->findBy(['dropReason' => DropReason::TIMEOUT]);
        $this->assertCount(1, $results);
        $this->assertEquals(DropReason::TIMEOUT, $results[0]->getDropReason());
    }

    public function testCountByAssociationUserProgress(): void
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
        $userProgress1->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress1);

        $userProgress2 = new UserProgress();
        $userProgress2->setTask($task);
        $userProgress2->setUserId('user2');
        $userProgress2->setCurrentNode($node);
        $userProgress2->setStatus(ProgressStatus::RUNNING);
        $userProgress2->setStartTime(new \DateTimeImmutable());
        $userProgress2->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress2);

        // Add multiple stages to userProgress1
        $stage1 = new NodeStage();
        $stage1->setUserProgress($userProgress1);
        $stage1->setNode($node);
        $stage1->setStatus(NodeStageStatus::PENDING);
        $stage1->setReachTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setUserProgress($userProgress1);
        $stage2->setNode($node);
        $stage2->setStatus(NodeStageStatus::RUNNING);
        $stage2->setReachTime(new \DateTimeImmutable());
        $stage2->setTouchTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage2, true);

        // Add only one stage to userProgress2
        $stage3 = new NodeStage();
        $stage3->setUserProgress($userProgress2);
        $stage3->setNode($node);
        $stage3->setStatus(NodeStageStatus::PENDING);
        $stage3->setReachTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage3, true);

        $count = $this->getRepository()->count(['userProgress' => $userProgress1]);
        $this->assertEquals(2, $count);
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

        $userProgress = new UserProgress();
        $userProgress->setTask($task);
        $userProgress->setUserId('user123');
        $userProgress->setCurrentNode($node);
        $userProgress->setStatus(ProgressStatus::RUNNING);
        $userProgress->setStartTime(new \DateTimeImmutable());
        $userProgress->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress);

        $stage1 = new NodeStage();
        $stage1->setUserProgress($userProgress);
        $stage1->setNode($node);
        $stage1->setStatus(NodeStageStatus::FINISHED);
        $stage1->setReachTime(new \DateTimeImmutable());
        $stage1->setFinishTime(new \DateTimeImmutable());
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setUserProgress($userProgress);
        $stage2->setNode($node);
        $stage2->setStatus(NodeStageStatus::RUNNING);
        $stage2->setReachTime(new \DateTimeImmutable());
        $stage2->setFinishTime(null);
        $this->getRepository()->save($stage2, true);

        $count = $this->getRepository()->count(['finishTime' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
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

        $userProgress = new UserProgress();
        $userProgress->setTask($task);
        $userProgress->setUserId('user123');
        $userProgress->setCurrentNode($node);
        $userProgress->setStatus(ProgressStatus::RUNNING);
        $userProgress->setStartTime(new \DateTimeImmutable());
        $userProgress->setStartTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($userProgress);

        $earlierTime = new \DateTimeImmutable('2024-01-01 09:00:00');
        $laterTime = new \DateTimeImmutable('2024-01-01 10:00:00');

        $stage1 = new NodeStage();
        $stage1->setUserProgress($userProgress);
        $stage1->setNode($node);
        $stage1->setStatus(NodeStageStatus::PENDING);
        $stage1->setReachTime($laterTime);
        $this->getRepository()->save($stage1, true);

        $stage2 = new NodeStage();
        $stage2->setUserProgress($userProgress);
        $stage2->setNode($node);
        $stage2->setStatus(NodeStageStatus::PENDING);
        $stage2->setReachTime($earlierTime);
        $this->getRepository()->save($stage2, true);

        $found = $this->getRepository()->findOneBy(
            ['status' => NodeStageStatus::PENDING],
            ['reachTime' => 'ASC']
        );

        $this->assertInstanceOf(NodeStage::class, $found);
        $this->assertEquals($earlierTime, $found->getReachTime());
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
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setName('Test Node ' . uniqid());
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $userProgress = new UserProgress();
        $userProgress->setTask($task);
        $userProgress->setUserId(uniqid());
        $userProgress->setStatus(ProgressStatus::RUNNING);
        $userProgress->setStartTime(new \DateTimeImmutable());
        $userProgress->setStartTime(new \DateTimeImmutable());
        $userProgress->setCurrentNode($node);
        self::getEntityManager()->persist($userProgress);

        $entity = new NodeStage();
        $entity->setStatus(NodeStageStatus::PENDING);
        $entity->setReachTime(new \DateTimeImmutable());
        $entity->setNode($node);
        $entity->setUserProgress($userProgress);

        return $entity;
    }
}
