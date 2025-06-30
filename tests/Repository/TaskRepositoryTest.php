<?php

namespace MarketingPlanBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Repository\TaskRepository;
use PHPUnit\Framework\TestCase;

class TaskRepositoryTest extends TestCase
{
    public function testInstantiation_createsRepository(): void
    {
        // Arrange
        $registry = $this->createMock(ManagerRegistry::class);

        // Act
        $repository = new TaskRepository($registry);

        // Assert
        $this->assertInstanceOf(TaskRepository::class, $repository);
    }
}
