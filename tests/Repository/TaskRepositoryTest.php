<?php

namespace MarketingPlanBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\TaskStatus;
use MarketingPlanBundle\Repository\TaskRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;
use UserTagBundle\Entity\Tag;

/**
 * @internal
 */
#[CoversClass(TaskRepository::class)]
#[RunTestsInSeparateProcesses]
final class TaskRepositoryTest extends AbstractRepositoryTestCase
{
    protected function createNewEntity(): object
    {
        $entity = new Task();
        $entity->setTitle('Test Task ' . uniqid());
        $entity->setStatus(TaskStatus::DRAFT);

        $tag = new Tag();
        $tag->setName('test-tag-' . uniqid());
        $entity->setCrowd($tag);

        $entity->setStartTime(new \DateTimeImmutable());
        $entity->setEndTime(new \DateTimeImmutable('+1 day'));

        return $entity;
    }

    protected function getRepository(): TaskRepository
    {
        return self::getService(TaskRepository::class);
    }

    protected function onSetUp(): void
    {
    }

    public function testInstantiationCreatesRepository(): void
    {
        $repository = self::getService(TaskRepository::class);
        $this->assertInstanceOf(TaskRepository::class, $repository);
    }

    public function testSave(): void
    {
        $repository = $this->getRepository();
        $tagMock = new Tag();
        $tagMock->setName('test-tag-' . uniqid());

        $entity = new Task();
        $entity->setTitle('Test Task');
        $entity->setCrowd($tagMock);
        $entity->setStartTime(new \DateTimeImmutable());
        $entity->setEndTime(new \DateTimeImmutable('+1 day'));
        $entity->setStatus(TaskStatus::DRAFT);

        $repository->save($entity);

        $this->assertNotEmpty($entity->getId());

        $found = $repository->find($entity->getId());
        $this->assertInstanceOf(Task::class, $found);
        $this->assertEquals('Test Task', $found->getTitle());
        $this->assertEquals(TaskStatus::DRAFT, $found->getStatus());
    }

    public function testRemove(): void
    {
        $repository = $this->getRepository();
        $tagMock = new Tag();
        $tagMock->setName('test-tag-' . uniqid());

        $entity = new Task();
        $entity->setTitle('Task to Remove');
        $entity->setCrowd($tagMock);
        $entity->setStartTime(new \DateTimeImmutable());
        $entity->setEndTime(new \DateTimeImmutable('+1 day'));
        $entity->setStatus(TaskStatus::RUNNING);

        $repository->save($entity);
        $id = $entity->getId();

        $repository->remove($entity);

        $found = $repository->find($id);
        $this->assertNull($found);
    }

    public function testFindOneByWithMatchingCriteriaShouldRespectOrderBy(): void
    {
        $repository = $this->getRepository();
        $tagMock = new Tag();
        $tagMock->setName('test-tag-' . uniqid());

        $entity1 = new Task();
        $entity1->setTitle('Z Task');
        $entity1->setCrowd($tagMock);
        $entity1->setStartTime(new \DateTimeImmutable());
        $entity1->setEndTime(new \DateTimeImmutable('+1 day'));
        $entity1->setStatus(TaskStatus::RUNNING);
        $repository->save($entity1);

        $entity2 = new Task();
        $entity2->setTitle('A Task');
        $entity2->setCrowd($tagMock);
        $entity2->setStartTime(new \DateTimeImmutable());
        $entity2->setEndTime(new \DateTimeImmutable('+2 days'));
        $entity2->setStatus(TaskStatus::RUNNING);
        $repository->save($entity2);

        $found = $repository->findOneBy(
            ['status' => TaskStatus::RUNNING],
            ['title' => 'ASC']
        );

        $this->assertInstanceOf(Task::class, $found);
        $this->assertEquals('A Task', $found->getTitle());
    }

    public function testFindByAssociationCrowd(): void
    {
        $repository = $this->getRepository();
        $tagMock1 = new Tag();
        $tagMock1->setName('test-tag-' . uniqid());
        $tagMock2 = new Tag();
        $tagMock2->setName('test-tag-' . uniqid());

        $entity1 = new Task();
        $entity1->setTitle('Task with Crowd 1');
        $entity1->setCrowd($tagMock1);
        $entity1->setStartTime(new \DateTimeImmutable());
        $entity1->setEndTime(new \DateTimeImmutable('+1 day'));
        $entity1->setStatus(TaskStatus::RUNNING);
        $repository->save($entity1);

        $entity2 = new Task();
        $entity2->setTitle('Task with Crowd 2');
        $entity2->setCrowd($tagMock2);
        $entity2->setStartTime(new \DateTimeImmutable());
        $entity2->setEndTime(new \DateTimeImmutable('+2 days'));
        $entity2->setStatus(TaskStatus::RUNNING);
        $repository->save($entity2);

        $results = $repository->findBy(['crowd' => $tagMock1]);
        $this->assertCount(1, $results);
        $this->assertEquals('Task with Crowd 1', $results[0]->getTitle());
    }

    public function testCountByAssociationCrowd(): void
    {
        $repository = $this->getRepository();
        $tagMock1 = new Tag();
        $tagMock1->setName('test-tag-' . uniqid());
        $tagMock2 = new Tag();
        $tagMock2->setName('test-tag-' . uniqid());

        $entity1 = new Task();
        $entity1->setTitle('Task 1 with Crowd 1');
        $entity1->setCrowd($tagMock1);
        $entity1->setStartTime(new \DateTimeImmutable());
        $entity1->setEndTime(new \DateTimeImmutable('+1 day'));
        $entity1->setStatus(TaskStatus::RUNNING);
        $repository->save($entity1);

        $entity2 = new Task();
        $entity2->setTitle('Task 2 with Crowd 1');
        $entity2->setCrowd($tagMock1);
        $entity2->setStartTime(new \DateTimeImmutable());
        $entity2->setEndTime(new \DateTimeImmutable('+2 days'));
        $entity2->setStatus(TaskStatus::DRAFT);
        $repository->save($entity2);

        $entity3 = new Task();
        $entity3->setTitle('Task with Crowd 2');
        $entity3->setCrowd($tagMock2);
        $entity3->setStartTime(new \DateTimeImmutable());
        $entity3->setEndTime(new \DateTimeImmutable('+3 days'));
        $entity3->setStatus(TaskStatus::RUNNING);
        $repository->save($entity3);

        $count = $repository->count(['crowd' => $tagMock1]);
        $this->assertEquals(2, $count);
    }

    public function testFindByNullableField(): void
    {
        $repository = $this->getRepository();
        $tagMock = new Tag();
        $tagMock->setName('test-tag-' . uniqid());

        $entity1 = new Task();
        $entity1->setTitle('Task with Description');
        $entity1->setCrowd($tagMock);
        $entity1->setStartTime(new \DateTimeImmutable());
        $entity1->setEndTime(new \DateTimeImmutable('+1 day'));
        $entity1->setStatus(TaskStatus::RUNNING);
        $entity1->setDescription('Some description');
        $repository->save($entity1);

        $entity2 = new Task();
        $entity2->setTitle('Task without Description');
        $entity2->setCrowd($tagMock);
        $entity2->setStartTime(new \DateTimeImmutable());
        $entity2->setEndTime(new \DateTimeImmutable('+2 days'));
        $entity2->setStatus(TaskStatus::RUNNING);
        $entity2->setDescription(null);
        $repository->save($entity2);

        $results = $repository->findBy(['description' => null]);
        $this->assertGreaterThanOrEqual(1, count($results));

        foreach ($results as $result) {
            $this->assertNull($result->getDescription());
        }
    }

    public function testCountByNullableField(): void
    {
        $repository = $this->getRepository();
        $tagMock = new Tag();
        $tagMock->setName('test-tag-' . uniqid());

        $entity1 = new Task();
        $entity1->setTitle('Task with Valid');
        $entity1->setCrowd($tagMock);
        $entity1->setStartTime(new \DateTimeImmutable());
        $entity1->setEndTime(new \DateTimeImmutable('+1 day'));
        $entity1->setStatus(TaskStatus::RUNNING);
        $entity1->setValid(true);
        $repository->save($entity1);

        $entity2 = new Task();
        $entity2->setTitle('Task without Valid');
        $entity2->setCrowd($tagMock);
        $entity2->setStartTime(new \DateTimeImmutable());
        $entity2->setEndTime(new \DateTimeImmutable('+2 days'));
        $entity2->setStatus(TaskStatus::RUNNING);
        $entity2->setValid(null);
        $repository->save($entity2);

        $count = $repository->count(['valid' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByWithMatchingCriteriaShouldRespectOrderByForTitle(): void
    {
        $repository = $this->getRepository();
        $tagMock = new Tag();
        $tagMock->setName('test-tag-' . uniqid());

        $entity1 = new Task();
        $entity1->setTitle('Z Task');
        $entity1->setCrowd($tagMock);
        $entity1->setStartTime(new \DateTimeImmutable());
        $entity1->setEndTime(new \DateTimeImmutable('+1 day'));
        $entity1->setStatus(TaskStatus::RUNNING);
        $repository->save($entity1);

        $entity2 = new Task();
        $entity2->setTitle('A Task');
        $entity2->setCrowd($tagMock);
        $entity2->setStartTime(new \DateTimeImmutable());
        $entity2->setEndTime(new \DateTimeImmutable('+2 days'));
        $entity2->setStatus(TaskStatus::RUNNING);
        $repository->save($entity2);

        $found = $repository->findOneBy(
            ['status' => TaskStatus::RUNNING],
            ['title' => 'ASC']
        );

        $this->assertInstanceOf(Task::class, $found);
        $this->assertEquals('A Task', $found->getTitle());
    }

    public function testFindOneByWithMatchingCriteriaShouldRespectOrderByForStatus(): void
    {
        $repository = $this->getRepository();
        $tagMock = new Tag();
        $tagMock->setName('test-tag-' . uniqid());

        $entity1 = new Task();
        $entity1->setTitle('Task Running');
        $entity1->setCrowd($tagMock);
        $entity1->setStartTime(new \DateTimeImmutable());
        $entity1->setEndTime(new \DateTimeImmutable('+1 day'));
        $entity1->setStatus(TaskStatus::RUNNING);
        $repository->save($entity1);

        $entity2 = new Task();
        $entity2->setTitle('Task Draft');
        $entity2->setCrowd($tagMock);
        $entity2->setStartTime(new \DateTimeImmutable());
        $entity2->setEndTime(new \DateTimeImmutable('+2 days'));
        $entity2->setStatus(TaskStatus::DRAFT);
        $repository->save($entity2);

        $found = $repository->findOneBy(
            [],
            ['status' => 'ASC']
        );

        $this->assertInstanceOf(Task::class, $found);
        $this->assertEquals(TaskStatus::DRAFT, $found->getStatus());
    }

    public function testFindByAssociationNodes(): void
    {
        $repository = $this->getRepository();
        $tagMock = new Tag();
        $tagMock->setName('test-tag-' . uniqid());

        $task1 = new Task();
        $task1->setTitle('Task 1');
        $task1->setCrowd($tagMock);
        $task1->setStartTime(new \DateTimeImmutable());
        $task1->setEndTime(new \DateTimeImmutable('+1 day'));
        $task1->setStatus(TaskStatus::RUNNING);
        $repository->save($task1);

        $task2 = new Task();
        $task2->setTitle('Task 2');
        $task2->setCrowd($tagMock);
        $task2->setStartTime(new \DateTimeImmutable());
        $task2->setEndTime(new \DateTimeImmutable('+2 days'));
        $task2->setStatus(TaskStatus::RUNNING);
        $repository->save($task2);

        // Create nodes
        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('none');
        $resourceConfig1->setAmount(0);

        $node1 = new Node();
        $node1->setName('Node 1');
        $node1->setType(NodeType::START);
        $node1->setSequence(1);
        $node1->setResource($resourceConfig1);
        $task1->addNode($node1);

        $resourceConfig2 = new ResourceConfig();
        $resourceConfig2->setType('none');
        $resourceConfig2->setAmount(0);

        $node2 = new Node();
        $node2->setName('Node 2');
        $node2->setType(NodeType::START);
        $node2->setSequence(1);
        $node2->setResource($resourceConfig2);
        $task2->addNode($node2);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($node1);
        $entityManager->persist($node2);
        $entityManager->flush();

        $this->assertEquals($task1, $node1->getTask());
        $this->assertEquals($task2, $node2->getTask());
        $this->assertCount(1, $task1->getNodes());
        $this->assertCount(1, $task2->getNodes());
    }

    public function testCountByAssociationNodes(): void
    {
        $repository = $this->getRepository();
        $tagMock = new Tag();
        $tagMock->setName('test-tag-' . uniqid());

        $task1 = new Task();
        $task1->setTitle('Task with Node 1');
        $task1->setCrowd($tagMock);
        $task1->setStartTime(new \DateTimeImmutable());
        $task1->setEndTime(new \DateTimeImmutable('+1 day'));
        $task1->setStatus(TaskStatus::RUNNING);
        $repository->save($task1);

        $task2 = new Task();
        $task2->setTitle('Task with Node 2');
        $task2->setCrowd($tagMock);
        $task2->setStartTime(new \DateTimeImmutable());
        $task2->setEndTime(new \DateTimeImmutable('+2 days'));
        $task2->setStatus(TaskStatus::RUNNING);
        $repository->save($task2);

        $task3 = new Task();
        $task3->setTitle('Task without Node');
        $task3->setCrowd($tagMock);
        $task3->setStartTime(new \DateTimeImmutable());
        $task3->setEndTime(new \DateTimeImmutable('+3 days'));
        $task3->setStatus(TaskStatus::DRAFT);
        $repository->save($task3);

        // Create nodes for first two tasks
        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('none');
        $resourceConfig1->setAmount(0);

        $node1 = new Node();
        $node1->setName('Node for Task 1');
        $node1->setType(NodeType::START);
        $node1->setSequence(1);
        $node1->setResource($resourceConfig1);
        $task1->addNode($node1);

        $resourceConfig2 = new ResourceConfig();
        $resourceConfig2->setType('none');
        $resourceConfig2->setAmount(0);

        $node2 = new Node();
        $node2->setName('Node for Task 2');
        $node2->setType(NodeType::START);
        $node2->setSequence(1);
        $node2->setResource($resourceConfig2);
        $task2->addNode($node2);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($node1);
        $entityManager->persist($node2);
        $entityManager->flush();

        $allTasks = $repository->findAll();
        $this->assertGreaterThanOrEqual(3, count($allTasks));
        $this->assertEquals($task1, $node1->getTask());
        $this->assertEquals($task2, $node2->getTask());
        $this->assertCount(1, $task1->getNodes());
        $this->assertCount(1, $task2->getNodes());
    }

    public function testFindByNullableFieldEndTime(): void
    {
        $repository = $this->getRepository();
        $tagMock = new Tag();
        $tagMock->setName('test-tag-' . uniqid());

        $entity1 = new Task();
        $entity1->setTitle('Task with End Time');
        $entity1->setCrowd($tagMock);
        $entity1->setStartTime(new \DateTimeImmutable());
        $entity1->setEndTime(new \DateTimeImmutable('+1 day'));
        $entity1->setStatus(TaskStatus::RUNNING);
        $repository->save($entity1);

        $entity2 = new Task();
        $entity2->setTitle('Task without End Time');
        $entity2->setCrowd($tagMock);
        $entity2->setStartTime(new \DateTimeImmutable());
        $entity2->setStatus(TaskStatus::DRAFT);
        $repository->save($entity2);

        $results = $repository->findBy(['endTime' => null]);
        $this->assertGreaterThanOrEqual(1, count($results));

        foreach ($results as $result) {
            $this->assertNull($result->getEndTime());
        }
    }

    public function testCountByNullableFieldEndTime(): void
    {
        $repository = $this->getRepository();
        $tagMock = new Tag();
        $tagMock->setName('test-tag-' . uniqid());

        $entity1 = new Task();
        $entity1->setTitle('Task with End Time');
        $entity1->setCrowd($tagMock);
        $entity1->setStartTime(new \DateTimeImmutable());
        $entity1->setEndTime(new \DateTimeImmutable('+1 day'));
        $entity1->setStatus(TaskStatus::RUNNING);
        $repository->save($entity1);

        $entity2 = new Task();
        $entity2->setTitle('Task without End Time');
        $entity2->setCrowd($tagMock);
        $entity2->setStartTime(new \DateTimeImmutable());
        $entity2->setStatus(TaskStatus::DRAFT);
        $repository->save($entity2);

        $count = $repository->count(['endTime' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByAssociationCrowdShouldReturnCorrectNumber(): void
    {
        $repository = $this->getRepository();
        $tagMock1 = new Tag();
        $tagMock1->setName('test-tag-' . uniqid());
        $tagMock2 = new Tag();
        $tagMock2->setName('test-tag-' . uniqid());

        $entity1 = new Task();
        $entity1->setTitle('Task 1 with Crowd 1');
        $entity1->setCrowd($tagMock1);
        $entity1->setStartTime(new \DateTimeImmutable());
        $entity1->setEndTime(new \DateTimeImmutable('+1 day'));
        $entity1->setStatus(TaskStatus::RUNNING);
        $repository->save($entity1);

        $entity2 = new Task();
        $entity2->setTitle('Task 2 with Crowd 1');
        $entity2->setCrowd($tagMock1);
        $entity2->setStartTime(new \DateTimeImmutable());
        $entity2->setEndTime(new \DateTimeImmutable('+2 days'));
        $entity2->setStatus(TaskStatus::DRAFT);
        $repository->save($entity2);

        $entity3 = new Task();
        $entity3->setTitle('Task with Crowd 2');
        $entity3->setCrowd($tagMock2);
        $entity3->setStartTime(new \DateTimeImmutable());
        $entity3->setEndTime(new \DateTimeImmutable('+3 days'));
        $entity3->setStatus(TaskStatus::RUNNING);
        $repository->save($entity3);

        $count = $repository->count(['crowd' => $tagMock1]);
        $this->assertEquals(2, $count);
    }

    public function testFindOneByAssociationCrowdShouldReturnMatchingEntity(): void
    {
        $repository = $this->getRepository();
        $tagMock = new Tag();
        $tagMock->setName('test-tag-' . uniqid());

        $entity = new Task();
        $entity->setTitle('Task with Specific Crowd');
        $entity->setCrowd($tagMock);
        $entity->setStartTime(new \DateTimeImmutable());
        $entity->setEndTime(new \DateTimeImmutable('+1 day'));
        $entity->setStatus(TaskStatus::FINISHED);
        $repository->save($entity);

        $found = $repository->findOneBy(['crowd' => $tagMock]);
        $this->assertInstanceOf(Task::class, $found);
        $this->assertEquals('Task with Specific Crowd', $found->getTitle());
        $this->assertEquals($tagMock, $found->getCrowd());
    }
}
