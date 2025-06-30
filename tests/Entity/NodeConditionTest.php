<?php

namespace MarketingPlanBundle\Tests\Entity;

use MarketingPlanBundle\Entity\NodeCondition;
use PHPUnit\Framework\TestCase;

class NodeConditionTest extends TestCase
{
    public function testEntity_canBeInstantiated(): void
    {
        // Arrange & Act
        $condition = new NodeCondition();

        // Assert
        $this->assertInstanceOf(NodeCondition::class, $condition);
    }
} 