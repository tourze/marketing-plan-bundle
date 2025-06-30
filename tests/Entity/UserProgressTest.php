<?php

namespace MarketingPlanBundle\Tests\Entity;

use MarketingPlanBundle\Entity\UserProgress;
use PHPUnit\Framework\TestCase;

class UserProgressTest extends TestCase
{
    public function testEntity_canBeInstantiated(): void
    {
        // Arrange & Act
        $progress = new UserProgress();

        // Assert
        $this->assertInstanceOf(UserProgress::class, $progress);
    }
} 