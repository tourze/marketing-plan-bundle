<?php

namespace MarketingPlanBundle\Tests\Entity;

use MarketingPlanBundle\Entity\NodeDelay;
use PHPUnit\Framework\TestCase;

class NodeDelayTest extends TestCase
{
    public function testEntity_canBeInstantiated(): void
    {
        // Arrange & Act
        $delay = new NodeDelay();

        // Assert
        $this->assertInstanceOf(NodeDelay::class, $delay);
    }
} 