<?php

namespace MarketingPlanBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeDelay;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\DelayType;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\TaskStatus;
use MarketingPlanBundle\Repository\NodeRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;
use UserTagBundle\Entity\Tag;

/**
 * @internal
 */
#[CoversClass(NodeRepository::class)]
#[RunTestsInSeparateProcesses]
final class NodeRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testInstantiationCreatesRepository(): void
    {
        $repository = self::getService(NodeRepository::class);
        $this->assertInstanceOf(NodeRepository::class, $repository);
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

        $entity = new Node();
        $entity->setName('Test Node ' . uniqid());
        $entity->setType(NodeType::START);
        $entity->setSequence(1);
        $entity->setTask($task);
        $entity->setResource($resourceConfig);

        return $entity;
    }

    private function createNodeWithResource(Task $task, string $name, NodeType $type, int $sequence = 1): Node
    {
        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $entity = new Node();
        $entity->setName($name);
        $entity->setType($type);
        $entity->setSequence($sequence);
        $entity->setTask($task);
        $entity->setResource($resourceConfig);

        return $entity;
    }

    protected function getRepository(): NodeRepository
    {
        return self::getService(NodeRepository::class);
    }

    public function testSave(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity = $this->createNodeWithResource($task, 'Test Node', NodeType::START, 1);

        $repository->save($entity);

        $this->assertGreaterThan(0, $entity->getId());

        $found = $repository->find($entity->getId());
        $this->assertInstanceOf(Node::class, $found);
        $this->assertEquals('Test Node', $found->getName());
        $this->assertEquals(NodeType::START, $found->getType());
    }

    public function testRemove(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity = $this->createNodeWithResource($task, 'Node to Remove', NodeType::END, 1);

        $repository->save($entity);
        $id = $entity->getId();

        $repository->remove($entity);

        $found = $repository->find($id);
        $this->assertNull($found);
    }

    public function testFindOneByWithMatchingCriteriaShouldRespectOrderBy(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = $this->createNodeWithResource($task, 'Node Z', NodeType::START, 2);
        $repository->save($entity1);

        $entity2 = $this->createNodeWithResource($task, 'Node A', NodeType::START, 1);
        $repository->save($entity2);

        $found = $repository->findOneBy(
            ['task' => $task, 'type' => NodeType::START],
            ['name' => 'ASC']
        );

        $this->assertInstanceOf(Node::class, $found);
        $this->assertEquals('Node A', $found->getName());
    }

    public function testFindByAssociationTask(): void
    {
        $repository = $this->getRepository();
        $task1 = $this->createTask();
        $task2 = $this->createTask();

        $entity1 = $this->createNodeWithResource($task1, 'Node with Task 1', NodeType::START, 1);
        $repository->save($entity1);

        $entity2 = $this->createNodeWithResource($task2, 'Node with Task 2', NodeType::START, 1);
        $repository->save($entity2);

        $results = $repository->findBy(['task' => $task1]);
        $this->assertCount(1, $results);
        $this->assertEquals('Node with Task 1', $results[0]->getName());
    }

    public function testCountByAssociationTask(): void
    {
        $repository = $this->getRepository();
        $task1 = $this->createTask();
        $task2 = $this->createTask();

        $entity1 = $this->createNodeWithResource($task1, 'Node 1 with Task 1', NodeType::START, 1);
        $repository->save($entity1);

        $entity2 = $this->createNodeWithResource($task1, 'Node 2 with Task 1', NodeType::END, 2);
        $repository->save($entity2);

        $entity3 = $this->createNodeWithResource($task2, 'Node with Task 2', NodeType::START, 1);
        $repository->save($entity3);

        $count = $repository->count(['task' => $task1]);
        $this->assertEquals(2, $count);
    }

    public function testFindByNullableField(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = $this->createNodeWithResource($task, 'Node with Resource', NodeType::START, 1);
        $repository->save($entity1);

        $entity2 = $this->createNodeWithResource($task, 'Node with Different Resource', NodeType::START, 2);
        $differentResource = new ResourceConfig();
        $differentResource->setType('test');
        $differentResource->setAmount(5);
        $entity2->setResource($differentResource);
        $repository->save($entity2);

        $results = $repository->findBy(['task' => $task, 'type' => NodeType::START]);
        $this->assertCount(2, $results);
    }

    public function testCountByNullableField(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = $this->createNodeWithResource($task, 'Node with Resource', NodeType::START, 1);
        $repository->save($entity1);

        $entity2 = $this->createNodeWithResource($task, 'Node with Different Resource', NodeType::START, 2);
        $differentResource = new ResourceConfig();
        $differentResource->setType('test');
        $differentResource->setAmount(5);
        $entity2->setResource($differentResource);
        $repository->save($entity2);

        $count = $repository->count(['task' => $task, 'type' => NodeType::START]);
        $this->assertEquals(2, $count);
    }

    public function testFindByTaskAndType(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = $this->createNodeWithResource($task, 'Start Node 1', NodeType::START, 1);
        $repository->save($entity1);

        $entity2 = $this->createNodeWithResource($task, 'Start Node 2', NodeType::START, 2);
        $repository->save($entity2);

        $entity3 = $this->createNodeWithResource($task, 'End Node', NodeType::END, 3);
        $repository->save($entity3);

        $taskId = $task->getId();
        $this->assertNotNull($taskId);
        $results = $repository->findByTaskAndType($taskId, NodeType::START);
        $this->assertCount(2, $results);

        foreach ($results as $result) {
            $this->assertInstanceOf(Node::class, $result);
            $this->assertEquals(NodeType::START, $result->getType());
        }

        // 验证按序列排序
        $this->assertEquals(1, $results[0]->getSequence());
        $this->assertEquals(2, $results[1]->getSequence());
    }

    public function testFindNextNodes(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = $this->createNodeWithResource($task, 'First Node', NodeType::START, 1);
        $repository->save($entity1);

        $entity2 = $this->createNodeWithResource($task, 'Second Node', NodeType::RESOURCE, 2);
        $repository->save($entity2);

        $entity3 = $this->createNodeWithResource($task, 'Third Node', NodeType::END, 3);
        $repository->save($entity3);

        $results = $repository->findNextNodes((string) $entity1->getId());
        $this->assertCount(2, $results);

        foreach ($results as $result) {
            $this->assertInstanceOf(Node::class, $result);
            $this->assertGreaterThan($entity1->getSequence(), $result->getSequence());
        }

        // 验证按序列排序
        $this->assertEquals(2, $results[0]->getSequence());
        $this->assertEquals(3, $results[1]->getSequence());
    }

    public function testFindOneByWithMatchingCriteriaShouldRespectOrderByForSequence(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = $this->createNodeWithResource($task, 'Node B', NodeType::START, 2);
        $repository->save($entity1);

        $entity2 = $this->createNodeWithResource($task, 'Node A', NodeType::START, 1);
        $repository->save($entity2);

        $found = $repository->findOneBy(
            ['task' => $task, 'type' => NodeType::START],
            ['sequence' => 'ASC']
        );

        $this->assertInstanceOf(Node::class, $found);
        $this->assertEquals(1, $found->getSequence());
        $this->assertEquals('Node A', $found->getName());
    }

    public function testFindByAssociationDelay(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = $this->createNodeWithResource($task, 'Node with Delay', NodeType::START, 1);
        $repository->save($entity1);

        $delay = new NodeDelay();
        $delay->setValue(60);
        $delay->setType(DelayType::MINUTES);
        $delay->setNode($entity1);  // Set the owning side first
        self::getEntityManager()->persist($delay);
        self::getEntityManager()->flush();

        $entity2 = $this->createNodeWithResource($task, 'Node without Delay', NodeType::START, 2);
        $repository->save($entity2);

        $results = $repository->findByDelay($delay);
        $this->assertCount(1, $results);
        $this->assertEquals('Node with Delay', $results[0]->getName());
    }

    public function testCountByAssociationDelay(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = $this->createNodeWithResource($task, 'Node 1 with Delay', NodeType::START, 1);
        $repository->save($entity1);

        $delay = new NodeDelay();
        $delay->setValue(120);
        $delay->setType(DelayType::MINUTES);
        $delay->setNode($entity1);
        self::getEntityManager()->persist($delay);
        self::getEntityManager()->flush();

        $entity2 = $this->createNodeWithResource($task, 'Node 2 with Delay', NodeType::RESOURCE, 2);
        $repository->save($entity2);

        $delay2 = new NodeDelay();
        $delay2->setValue(120);
        $delay2->setType(DelayType::MINUTES);
        $delay2->setNode($entity2);
        self::getEntityManager()->persist($delay2);
        self::getEntityManager()->flush();

        $entity3 = $this->createNodeWithResource($task, 'Node without Delay', NodeType::END, 3);
        $repository->save($entity3);

        $count = $repository->countByDelay($delay);
        $this->assertEquals(1, $count);
    }

    public function testFindByNullableFieldDelay(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = $this->createNodeWithResource($task, 'Node with Delay', NodeType::START, 1);
        $repository->save($entity1);

        $delay = new NodeDelay();
        $delay->setValue(30);
        $delay->setType(DelayType::MINUTES);
        $delay->setNode($entity1);
        self::getEntityManager()->persist($delay);
        self::getEntityManager()->flush();

        $entity2 = $this->createNodeWithResource($task, 'Node without Delay', NodeType::START, 2);
        $entity2->setDelay(null);
        $repository->save($entity2);

        $results = $repository->findByDelay(null);
        $this->assertGreaterThanOrEqual(1, count($results));

        foreach ($results as $result) {
            $this->assertNull($result->getDelay());
        }
    }

    public function testCountByNullableFieldDelay(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = $this->createNodeWithResource($task, 'Node with Delay', NodeType::START, 1);
        $repository->save($entity1);

        $delay = new NodeDelay();
        $delay->setValue(15);
        $delay->setType(DelayType::MINUTES);
        $delay->setNode($entity1);
        self::getEntityManager()->persist($delay);
        self::getEntityManager()->flush();

        $entity2 = $this->createNodeWithResource($task, 'Node without Delay', NodeType::START, 2);
        $entity2->setDelay(null);
        $repository->save($entity2);

        $count = $repository->countByDelay(null);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByShouldRespectOrderBy(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = $this->createNodeWithResource($task, 'Node Z', NodeType::START, 2);
        $repository->save($entity1);

        $entity2 = $this->createNodeWithResource($task, 'Node A', NodeType::START, 1);
        $repository->save($entity2);

        $found = $repository->findOneBy(
            ['task' => $task, 'type' => NodeType::START],
            ['sequence' => 'ASC']
        );

        $this->assertInstanceOf(Node::class, $found);
        $this->assertEquals(1, $found->getSequence());
        $this->assertEquals('Node A', $found->getName());
    }

    public function testCountByDelay(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        // 创建节点和延迟
        $entity1 = $this->createNodeWithResource($task, 'Node 1 with specific delay', NodeType::START, 1);
        $repository->save($entity1);

        $delay1 = new NodeDelay();
        $delay1->setValue(60);
        $delay1->setType(DelayType::MINUTES);
        $delay1->setNode($entity1);
        self::getEntityManager()->persist($delay1);

        $entity2 = $this->createNodeWithResource($task, 'Node 2 with same delay value', NodeType::RESOURCE, 2);
        $repository->save($entity2);

        $delay2 = new NodeDelay();
        $delay2->setValue(60);
        $delay2->setType(DelayType::MINUTES);
        $delay2->setNode($entity2);
        self::getEntityManager()->persist($delay2);

        $entity3 = $this->createNodeWithResource($task, 'Node 3 with different delay', NodeType::END, 3);
        $repository->save($entity3);

        $delay3 = new NodeDelay();
        $delay3->setValue(120);
        $delay3->setType(DelayType::MINUTES);
        $delay3->setNode($entity3);
        self::getEntityManager()->persist($delay3);

        $entity4 = $this->createNodeWithResource($task, 'Node 4 without delay', NodeType::START, 4);
        $repository->save($entity4);

        self::getEntityManager()->flush();

        // 测试根据特定 delay 对象计数
        $count1 = $repository->countByDelay($delay1);
        $this->assertEquals(1, $count1);

        $count2 = $repository->countByDelay($delay2);
        $this->assertEquals(1, $count2);

        $count3 = $repository->countByDelay($delay3);
        $this->assertEquals(1, $count3);

        // 测试 null 延迟的计数
        $countNull = $repository->countByDelay(null);
        $this->assertGreaterThanOrEqual(1, $countNull);
    }

    public function testFindByDelay(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        // 创建节点和延迟
        $entity1 = $this->createNodeWithResource($task, 'Node 1 with specific delay', NodeType::START, 1);
        $repository->save($entity1);

        $delay1 = new NodeDelay();
        $delay1->setValue(90);
        $delay1->setType(DelayType::MINUTES);
        $delay1->setNode($entity1);
        self::getEntityManager()->persist($delay1);

        $entity2 = $this->createNodeWithResource($task, 'Node 2 with different delay', NodeType::RESOURCE, 2);
        $repository->save($entity2);

        $delay2 = new NodeDelay();
        $delay2->setValue(180);
        $delay2->setType(DelayType::MINUTES);
        $delay2->setNode($entity2);
        self::getEntityManager()->persist($delay2);

        $entity3 = $this->createNodeWithResource($task, 'Node 3 without delay', NodeType::END, 3);
        $repository->save($entity3);

        self::getEntityManager()->flush();

        // 测试根据特定 delay 对象查找
        $results1 = $repository->findByDelay($delay1);
        $this->assertCount(1, $results1);
        $this->assertEquals('Node 1 with specific delay', $results1[0]->getName());
        $this->assertSame($entity1, $results1[0]);

        $results2 = $repository->findByDelay($delay2);
        $this->assertCount(1, $results2);
        $this->assertEquals('Node 2 with different delay', $results2[0]->getName());
        $this->assertSame($entity2, $results2[0]);

        // 测试查找没有延迟的节点
        $resultsNull = $repository->findByDelay(null);
        $this->assertGreaterThanOrEqual(1, count($resultsNull));

        // 验证返回的节点确实没有延迟
        foreach ($resultsNull as $result) {
            $this->assertNull($result->getDelay());
        }

        // 测试使用不存在的延迟对象查找
        $nonExistentDelay = new NodeDelay();
        $nonExistentDelay->setValue(999);
        $nonExistentDelay->setType(DelayType::MINUTES);

        $emptyResults = $repository->findByDelay($nonExistentDelay);
        $this->assertCount(0, $emptyResults);
    }

    private function createTask(): Task
    {
        $entityManager = self::getService(EntityManagerInterface::class);

        // 创建一个简单的测试标签，而不是使用模拟
        $tag = new Tag();
        $tag->setName('test-tag-' . uniqid());

        $task = new Task();
        $task->setTitle('Test Task ' . uniqid());
        $task->setCrowd($tag);
        $task->setStartTime(new \DateTimeImmutable());
        $task->setEndTime(new \DateTimeImmutable('+1 day'));

        $entityManager->persist($task);
        $entityManager->flush();

        return $task;
    }
}
