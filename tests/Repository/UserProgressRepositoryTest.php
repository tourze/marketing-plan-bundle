<?php

namespace MarketingPlanBundle\Tests\Repository;

use MarketingPlanBundle\Repository\UserProgressRepository;
use PHPUnit\Framework\TestCase;

class UserProgressRepositoryTest extends TestCase
{
    public function testRepository_canBeInstantiated(): void
    {
        // 基础测试类存在验证
        $this->assertTrue(class_exists(UserProgressRepository::class));
    }
} 