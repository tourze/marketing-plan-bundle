<?php

namespace MarketingPlanBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use MarketingPlanBundle\Entity\Log;
use MarketingPlanBundle\Repository\LogRepository;
use PHPUnit\Framework\TestCase;

class LogRepositoryTest extends TestCase
{
    public function testInstantiation_createsRepository(): void
    {
        // Arrange
        $registry = $this->createMock(ManagerRegistry::class);

        // Act
        $repository = new LogRepository($registry);

        // Assert
        $this->assertInstanceOf(LogRepository::class, $repository);
    }
}
