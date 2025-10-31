<?php

namespace MarketingPlanBundle\Tests\Repository;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeDelay;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\DelayType;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\TaskStatus;
use MarketingPlanBundle\Repository\NodeDelayRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;
use UserTagBundle\Entity\Tag;

/**
 * @internal
 */
#[CoversClass(NodeDelayRepository::class)]
#[RunTestsInSeparateProcesses]
final class NodeDelayRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 可以在这里添加测试初始化逻辑
    }

    /**
     * 测试 node 字段的查询（现在 NodeDelay 是 owning side，可以正常查询）
     */
    public function testNodeAssociationCanBeUsedInFindBy(): void
    {
        // 现在 node 字段是 owning side，可以正常查询
        $result = $this->getRepository()->findBy(['node' => null]);
        $this->assertIsArray($result);
    }

    protected function getRepository(): NodeDelayRepository
    {
        return self::getService(NodeDelayRepository::class);
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
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $delay = new NodeDelay();
        $delay->setType(DelayType::MINUTES);
        $delay->setValue(30);
        $delay->setNode($node);

        $this->getRepository()->save($delay, true);

        $this->assertGreaterThan(0, $delay->getId());
        $this->assertSame(DelayType::MINUTES, $delay->getType());
        $this->assertSame(30, $delay->getValue());
        $this->assertSame($node, $delay->getNode());
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
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $delay = new NodeDelay();
        $delay->setType(DelayType::MINUTES);
        $delay->setValue(30);
        $delay->setNode($node);

        $this->getRepository()->save($delay, true);
        $delayId = $delay->getId();

        $this->getRepository()->remove($delay, true);

        $removedDelay = $this->getRepository()->find($delayId);
        $this->assertNull($removedDelay);
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
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $delay = new NodeDelay();
        $delay->setType(DelayType::MINUTES);
        $delay->setValue(30);
        $delay->setNode($node);

        $this->getRepository()->save($delay, true);
        $delayId = $delay->getId();

        $foundDelay = $this->getRepository()->find($delayId);
        $this->assertInstanceOf(NodeDelay::class, $foundDelay);
        $this->assertSame($delayId, $foundDelay->getId());
        $this->assertSame(DelayType::MINUTES, $foundDelay->getType());
        $this->assertSame(30, $foundDelay->getValue());
    }

    public function testFindAll(): void
    {
        // 清理现有的 NodeDelay 数据
        self::getEntityManager()->createQuery('DELETE FROM ' . NodeDelay::class)->execute();

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('none');
        $resourceConfig1->setAmount(0);

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

        $delay1 = new NodeDelay();
        $delay1->setType(DelayType::MINUTES);
        $delay1->setValue(30);
        $delay1->setNode($node1);
        $this->getRepository()->save($delay1, true);

        $delay2 = new NodeDelay();
        $delay2->setType(DelayType::HOURS);
        $delay2->setValue(2);
        $delay2->setNode($node2);
        $this->getRepository()->save($delay2, true);

        $delays = $this->getRepository()->findAll();
        $this->assertCount(2, $delays);
        $this->assertContainsOnlyInstancesOf(NodeDelay::class, $delays);
    }

    public function testFindBy(): void
    {
        // 清理现有的 NodeDelay 数据
        self::getEntityManager()->createQuery('DELETE FROM ' . NodeDelay::class)->execute();

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('none');
        $resourceConfig1->setAmount(0);

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

        $delay1 = new NodeDelay();
        $delay1->setType(DelayType::MINUTES);
        $delay1->setValue(30);
        $delay1->setNode($node1);
        $this->getRepository()->save($delay1, true);

        $delay2 = new NodeDelay();
        $delay2->setType(DelayType::HOURS);
        $delay2->setValue(2);
        $delay2->setNode($node2);
        $this->getRepository()->save($delay2, true);

        $minuteDelays = $this->getRepository()->findBy(['type' => DelayType::MINUTES]);
        $this->assertCount(1, $minuteDelays);
        $this->assertSame(DelayType::MINUTES, $minuteDelays[0]->getType());

        $hourDelays = $this->getRepository()->findBy(['type' => DelayType::HOURS]);
        $this->assertCount(1, $hourDelays);
        $this->assertSame(DelayType::HOURS, $hourDelays[0]->getType());
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

        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('none');
        $resourceConfig1->setAmount(0);

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

        $delay1 = new NodeDelay();
        $delay1->setType(DelayType::MINUTES);
        $delay1->setValue(10);
        $delay1->setNode($node1);
        $this->getRepository()->save($delay1, true);

        $delay2 = new NodeDelay();
        $delay2->setType(DelayType::MINUTES);
        $delay2->setValue(30);
        $delay2->setNode($node2);
        $this->getRepository()->save($delay2, true);

        $delays = $this->getRepository()->findBy(
            ['type' => DelayType::MINUTES],
            ['value' => 'DESC'],
            1,
            0
        );

        $this->assertCount(1, $delays);
        $this->assertSame(30, $delays[0]->getValue());
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
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $delay = new NodeDelay();
        $delay->setType(DelayType::MINUTES);
        $delay->setValue(45);
        $delay->setNode($node);
        $this->getRepository()->save($delay, true);

        $foundDelay = $this->getRepository()->findOneBy(['value' => 45]);
        $this->assertInstanceOf(NodeDelay::class, $foundDelay);
        $this->assertSame(45, $foundDelay->getValue());
        $this->assertSame(DelayType::MINUTES, $foundDelay->getType());

        $notFoundDelay = $this->getRepository()->findOneBy(['value' => 999]);
        $this->assertNull($notFoundDelay);
    }

    public function testCount(): void
    {
        // 清理现有的 NodeDelay 数据
        self::getEntityManager()->createQuery('DELETE FROM ' . NodeDelay::class)->execute();

        $initialCount = $this->getRepository()->count([]);

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('none');
        $resourceConfig1->setAmount(0);

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

        $delay1 = new NodeDelay();
        $delay1->setType(DelayType::MINUTES);
        $delay1->setValue(30);
        $delay1->setNode($node1);
        $this->getRepository()->save($delay1, true);

        $delay2 = new NodeDelay();
        $delay2->setType(DelayType::HOURS);
        $delay2->setValue(2);
        $delay2->setNode($node2);
        $this->getRepository()->save($delay2, true);

        $totalCount = $this->getRepository()->count([]);
        $this->assertSame($initialCount + 2, $totalCount);

        $minuteCount = $this->getRepository()->count(['type' => DelayType::MINUTES]);
        $this->assertSame(1, $minuteCount);
    }

    public function testFindByType(): void
    {
        // 清理现有的 NodeDelay 数据
        self::getEntityManager()->createQuery('DELETE FROM ' . NodeDelay::class)->execute();

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('none');
        $resourceConfig1->setAmount(0);

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

        $delay1 = new NodeDelay();
        $delay1->setType(DelayType::MINUTES);
        $delay1->setValue(30);
        $delay1->setNode($node1);
        $this->getRepository()->save($delay1, true);

        $delay2 = new NodeDelay();
        $delay2->setType(DelayType::DAYS);
        $delay2->setValue(1);
        $delay2->setNode($node2);
        $this->getRepository()->save($delay2, true);

        $minuteDelays = $this->getRepository()->findBy(['type' => DelayType::MINUTES]);
        $this->assertCount(1, $minuteDelays);
        $this->assertSame(DelayType::MINUTES, $minuteDelays[0]->getType());

        $dayDelays = $this->getRepository()->findBy(['type' => DelayType::DAYS]);
        $this->assertCount(1, $dayDelays);
        $this->assertSame(DelayType::DAYS, $dayDelays[0]->getType());
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
        $resourceConfig1->setType('none');
        $resourceConfig1->setAmount(0);

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

        $delay1 = new NodeDelay();
        $delay1->setType(DelayType::MINUTES);
        $delay1->setValue(30);
        $delay1->setNode($node1);
        $this->getRepository()->save($delay1, true);

        $delay2 = new NodeDelay();
        $delay2->setType(DelayType::HOURS);
        $delay2->setValue(2);
        $delay2->setNode($node2);
        $this->getRepository()->save($delay2, true);

        $node1Delays = $this->getRepository()->findBy(['node' => $node1]);
        $this->assertCount(1, $node1Delays);
        $this->assertSame($node1, $node1Delays[0]->getNode());

        $node2Delays = $this->getRepository()->findBy(['node' => $node2]);
        $this->assertCount(1, $node2Delays);
        $this->assertSame($node2, $node2Delays[0]->getNode());
    }

    public function testFindBySpecificTime(): void
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
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $specificTime = new \DateTimeImmutable('2024-01-15 10:00:00');

        $delay = new NodeDelay();
        $delay->setType(DelayType::SPECIFIC_TIME);
        $delay->setValue(0);
        $delay->setSpecificTime($specificTime);
        $delay->setNode($node);
        $this->getRepository()->save($delay, true);

        $foundDelays = $this->getRepository()->findBy(['type' => DelayType::SPECIFIC_TIME]);
        $this->assertCount(1, $foundDelays);
        $this->assertSame(DelayType::SPECIFIC_TIME, $foundDelays[0]->getType());
        $this->assertEquals($specificTime, $foundDelays[0]->getSpecificTime());
    }

    public function testFindByNullSpecificTime(): void
    {
        $delays = $this->getRepository()->findBy(['specificTime' => null]);
        $this->assertIsArray($delays);
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
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $delay1 = new NodeDelay();
        $delay1->setType(DelayType::MINUTES);
        $delay1->setValue(30);
        $delay1->setNode($node);
        $this->getRepository()->save($delay1, true);

        // 创建第二个 Node 和 delay，因为 OneToOne 关系不允许一个 Node 有多个 delay
        $resourceConfig2 = new ResourceConfig();
        $resourceConfig2->setType('points');
        $resourceConfig2->setAmount(100);

        $node2 = new Node();
        $node2->setName('Test Node 2');
        $node2->setType(NodeType::RESOURCE);
        $node2->setTask($task);
        $node2->setResource($resourceConfig2);
        self::getEntityManager()->persist($node2);

        $delay2 = new NodeDelay();
        $delay2->setType(DelayType::MINUTES);
        $delay2->setValue(60);
        $delay2->setNode($node2);
        $this->getRepository()->save($delay2, true);

        $found = $this->getRepository()->findOneBy(['type' => DelayType::MINUTES], ['value' => 'DESC']);
        $this->assertInstanceOf(NodeDelay::class, $found);
        $this->assertEquals(60, $found->getValue());
    }

    public function testFindOneByAssociationNodeShouldReturnMatchingEntity(): void
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
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $delay = new NodeDelay();
        $delay->setType(DelayType::MINUTES);
        $delay->setValue(45);
        $delay->setNode($node);
        $this->getRepository()->save($delay, true);

        $found = $this->getRepository()->findOneBy(['node' => $node]);
        $this->assertInstanceOf(NodeDelay::class, $found);
        $this->assertSame($node, $found->getNode());
        $this->assertEquals(45, $found->getValue());
    }

    public function testCountByAssociationNodeShouldReturnCorrectNumber(): void
    {
        // 清理现有的 NodeDelay 数据
        self::getEntityManager()->createQuery('DELETE FROM ' . NodeDelay::class)->execute();

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('none');
        $resourceConfig1->setAmount(0);

        $node1 = new Node();
        $node1->setName('Node 1');
        $node1->setType(NodeType::START);
        $node1->setTask($task);
        $node1->setResource($resourceConfig1);
        self::getEntityManager()->persist($node1);

        $node2 = new Node();
        $node2->setName('Node 2');
        $node2->setType(NodeType::RESOURCE);
        $node2->setTask($task);
        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('points');
        $node2->setResource($resourceConfig);
        self::getEntityManager()->persist($node2);

        // 为 node1 创建一个延时
        $delay1 = new NodeDelay();
        $delay1->setType(DelayType::MINUTES);
        $delay1->setValue(15);
        $delay1->setNode($node1);
        $this->getRepository()->save($delay1, true);

        // 为 node2 创建一个延时
        $delay2 = new NodeDelay();
        $delay2->setType(DelayType::HOURS);
        $delay2->setValue(1);
        $delay2->setNode($node2);
        $this->getRepository()->save($delay2, true);

        // 验证总共有2个延时记录
        $totalCount = $this->getRepository()->count([]);
        $this->assertSame(2, $totalCount);

        // 验证 MINUTES 类型的延时有1个
        $minutesCount = $this->getRepository()->count(['type' => DelayType::MINUTES]);
        $this->assertSame(1, $minutesCount);
    }

    public function testFindByTypeAndValue(): void
    {
        $results = $this->getRepository()->findByTypeAndValue(DelayType::MINUTES, 30);
        $this->assertIsArray($results);

        $noResults = $this->getRepository()->findByTypeAndValue(DelayType::DAYS, 99);
        $this->assertIsArray($noResults);
    }

    public function testFindPendingDelays(): void
    {
        $pendingDelays = $this->getRepository()->findPendingDelays();
        $this->assertIsArray($pendingDelays);
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

        $entity = new NodeDelay();
        $entity->setType(DelayType::MINUTES);
        $entity->setValue(5);
        $entity->setNode($node);

        return $entity;
    }
}
