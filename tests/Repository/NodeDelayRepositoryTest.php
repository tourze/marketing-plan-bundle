<?php

namespace MarketingPlanBundle\Tests\Repository;

use MarketingPlanBundle\Repository\NodeDelayRepository;
use PHPUnit\Framework\TestCase;

class NodeDelayRepositoryTest extends TestCase
{
    public function testRepository_canBeInstantiated(): void
    {
        // 基础测试类存在验证
        $this->assertTrue(class_exists(NodeDelayRepository::class));
    }
}
