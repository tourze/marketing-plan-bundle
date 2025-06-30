<?php

namespace MarketingPlanBundle\Tests\Repository;

use MarketingPlanBundle\Repository\NodeConditionRepository;
use PHPUnit\Framework\TestCase;

class NodeConditionRepositoryTest extends TestCase
{
    public function testRepository_canBeInstantiated(): void
    {
        // 基础测试类存在验证
        $this->assertTrue(class_exists(NodeConditionRepository::class));
    }
} 