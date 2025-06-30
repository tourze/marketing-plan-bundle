<?php

namespace MarketingPlanBundle\Tests\Entity;

use MarketingPlanBundle\Entity\NodeStage;
use PHPUnit\Framework\TestCase;

class NodeStageTest extends TestCase
{
    public function testEntity_canBeInstantiated(): void
    {
        // Arrange & Act
        $stage = new NodeStage();

        // Assert
        $this->assertInstanceOf(NodeStage::class, $stage);
    }
}
