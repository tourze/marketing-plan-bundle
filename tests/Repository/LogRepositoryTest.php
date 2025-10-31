<?php

namespace MarketingPlanBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use MarketingPlanBundle\Entity\Log;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\LogStatus;
use MarketingPlanBundle\Repository\LogRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserTagBundle\Entity\Tag;

/**
 * @internal
 */
#[CoversClass(LogRepository::class)]
#[RunTestsInSeparateProcesses]
final class LogRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testInstantiationCreatesRepository(): void
    {
        $repository = self::getService(LogRepository::class);
        $this->assertInstanceOf(LogRepository::class, $repository);
    }

    protected function createNewEntity(): object
    {
        $entity = new Log();
        $entity->setTask($this->createTask());
        $entity->setUserId('test-user-' . uniqid());
        $entity->setStatus(LogStatus::IN_PROGRESS);

        return $entity;
    }

    protected function getRepository(): LogRepository
    {
        return self::getService(LogRepository::class);
    }

    public function testSave(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity = new Log();
        $entity->setTask($task);
        $entity->setUserId('user123');
        $entity->setStatus(LogStatus::IN_PROGRESS);

        $repository->save($entity);

        $this->assertGreaterThan(0, $entity->getId());

        $found = $repository->find($entity->getId());
        $this->assertInstanceOf(Log::class, $found);
        $this->assertEquals('user123', $found->getUserId());
        $this->assertEquals(LogStatus::IN_PROGRESS, $found->getStatus());
    }

    public function testRemove(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity = new Log();
        $entity->setTask($task);
        $entity->setUserId('user456');
        $entity->setStatus(LogStatus::COMPLETED);

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

        $entity1 = new Log();
        $entity1->setTask($task);
        $entity1->setUserId('user_z');
        $entity1->setStatus(LogStatus::IN_PROGRESS);
        $repository->save($entity1);

        $entity2 = new Log();
        $entity2->setTask($task);
        $entity2->setUserId('user_a');
        $entity2->setStatus(LogStatus::IN_PROGRESS);
        $repository->save($entity2);

        $found = $repository->findOneBy(
            ['task' => $task, 'status' => LogStatus::IN_PROGRESS],
            ['userId' => 'ASC']
        );

        $this->assertInstanceOf(Log::class, $found);
        $this->assertEquals('user_a', $found->getUserId());
    }

    public function testFindByNullableField(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = new Log();
        $entity1->setTask($task);
        $entity1->setUserId('user1');
        $entity1->setStatus(LogStatus::IN_PROGRESS);
        $entity1->setContext(['key' => 'value']);
        $repository->save($entity1);

        $entity2 = new Log();
        $entity2->setTask($task);
        $entity2->setUserId('user2');
        $entity2->setStatus(LogStatus::IN_PROGRESS);
        $entity2->setContext(null);
        $repository->save($entity2);

        $results = $repository->findBy(['context' => null]);
        $this->assertGreaterThanOrEqual(1, count($results));

        foreach ($results as $result) {
            $this->assertNull($result->getContext());
        }
    }

    public function testCountByNullableField(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = new Log();
        $entity1->setTask($task);
        $entity1->setUserId('user1');
        $entity1->setStatus(LogStatus::IN_PROGRESS);
        $entity1->setFailureReason('Some error');
        $repository->save($entity1);

        $entity2 = new Log();
        $entity2->setTask($task);
        $entity2->setUserId('user2');
        $entity2->setStatus(LogStatus::IN_PROGRESS);
        $entity2->setFailureReason(null);
        $repository->save($entity2);

        $count = $repository->count(['failureReason' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByNullableCompleteTime(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = new Log();
        $entity1->setTask($task);
        $entity1->setUserId('user1');
        $entity1->setStatus(LogStatus::COMPLETED);
        $entity1->setCompleteTime(new \DateTimeImmutable());
        $repository->save($entity1);

        $entity2 = new Log();
        $entity2->setTask($task);
        $entity2->setUserId('user2');
        $entity2->setStatus(LogStatus::IN_PROGRESS);
        $entity2->setCompleteTime(null);
        $repository->save($entity2);

        $results = $repository->findBy(['completeTime' => null]);
        $this->assertGreaterThanOrEqual(1, count($results));

        foreach ($results as $result) {
            $this->assertNull($result->getCompleteTime());
        }
    }

    public function testCountByNullableCompleteTime(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = new Log();
        $entity1->setTask($task);
        $entity1->setUserId('user1');
        $entity1->setStatus(LogStatus::COMPLETED);
        $entity1->setCompleteTime(new \DateTimeImmutable());
        $repository->save($entity1);

        $entity2 = new Log();
        $entity2->setTask($task);
        $entity2->setUserId('user2');
        $entity2->setStatus(LogStatus::IN_PROGRESS);
        $entity2->setCompleteTime(null);
        $repository->save($entity2);

        $count = $repository->count(['completeTime' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByNullableProgressData(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = new Log();
        $entity1->setTask($task);
        $entity1->setUserId('user1');
        $entity1->setStatus(LogStatus::IN_PROGRESS);
        $entity1->setProgressData(['step' => 1]);
        $repository->save($entity1);

        $entity2 = new Log();
        $entity2->setTask($task);
        $entity2->setUserId('user2');
        $entity2->setStatus(LogStatus::IN_PROGRESS);
        $entity2->setProgressData(null);
        $repository->save($entity2);

        $results = $repository->findBy(['progressData' => null]);
        $this->assertGreaterThanOrEqual(1, count($results));

        foreach ($results as $result) {
            $this->assertNull($result->getProgressData());
        }
    }

    public function testCountByNullableProgressData(): void
    {
        $repository = $this->getRepository();
        $task = $this->createTask();

        $entity1 = new Log();
        $entity1->setTask($task);
        $entity1->setUserId('user1');
        $entity1->setStatus(LogStatus::IN_PROGRESS);
        $entity1->setProgressData(['step' => 1]);
        $repository->save($entity1);

        $entity2 = new Log();
        $entity2->setTask($task);
        $entity2->setUserId('user2');
        $entity2->setStatus(LogStatus::IN_PROGRESS);
        $entity2->setProgressData(null);
        $repository->save($entity2);

        $count = $repository->count(['progressData' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByAssociationTaskShouldReturnMatchingEntity(): void
    {
        $repository = $this->getRepository();
        $task1 = $this->createTask();
        $task2 = $this->createTask();

        $entity1 = new Log();
        $entity1->setTask($task1);
        $entity1->setUserId('user1');
        $entity1->setStatus(LogStatus::IN_PROGRESS);
        $repository->save($entity1);

        $entity2 = new Log();
        $entity2->setTask($task2);
        $entity2->setUserId('user2');
        $entity2->setStatus(LogStatus::IN_PROGRESS);
        $repository->save($entity2);

        $found = $repository->findOneBy(['task' => $task1]);
        $this->assertInstanceOf(Log::class, $found);
        $this->assertEquals($task1->getId(), $found->getTask()->getId());
        $this->assertEquals('user1', $found->getUserId());
    }

    public function testCountByAssociationTaskShouldReturnCorrectNumber(): void
    {
        $repository = $this->getRepository();
        $task1 = $this->createTask();
        $task2 = $this->createTask();

        for ($i = 1; $i <= 3; ++$i) {
            $entity = new Log();
            $entity->setTask($task1);
            $entity->setUserId('user' . $i);
            $entity->setStatus(LogStatus::IN_PROGRESS);
            $repository->save($entity);
        }

        for ($i = 1; $i <= 2; ++$i) {
            $entity = new Log();
            $entity->setTask($task2);
            $entity->setUserId('user' . ($i + 3));
            $entity->setStatus(LogStatus::IN_PROGRESS);
            $repository->save($entity);
        }

        $count = $repository->count(['task' => $task1]);
        $this->assertEquals(3, $count);
    }

    private function createTask(): Task
    {
        $entityManager = self::getService(EntityManagerInterface::class);
        $tagMock = new Tag();
        $tagMock->setName('test-tag-' . uniqid());

        $task = new Task();
        $task->setTitle('Test Task ' . uniqid());
        $task->setCrowd($tagMock);
        $task->setStartTime(new \DateTimeImmutable());
        $task->setEndTime(new \DateTimeImmutable('+1 day'));

        $entityManager->persist($task);
        $entityManager->flush();

        return $task;
    }
}
