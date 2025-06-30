<?php

namespace MarketingPlanBundle\Tests\Unit\Enum;

use MarketingPlanBundle\Enum\LogStatus;
use PHPUnit\Framework\TestCase;

class LogStatusTest extends TestCase
{
    public function testCases(): void
    {
        $cases = LogStatus::cases();
        $this->assertCount(4, $cases);
        
        $expectedCases = [
            LogStatus::IN_PROGRESS,
            LogStatus::COMPLETED,
            LogStatus::FAILED,
            LogStatus::CANCELLED,
        ];
        
        foreach ($expectedCases as $expectedCase) {
            $this->assertContains($expectedCase, $cases);
        }
    }

    public function testValues(): void
    {
        $this->assertSame('in_progress', LogStatus::IN_PROGRESS->value);
        $this->assertSame('completed', LogStatus::COMPLETED->value);
        $this->assertSame('failed', LogStatus::FAILED->value);
        $this->assertSame('cancelled', LogStatus::CANCELLED->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('进行中', LogStatus::IN_PROGRESS->getLabel());
        $this->assertSame('已完成', LogStatus::COMPLETED->getLabel());
        $this->assertSame('失败', LogStatus::FAILED->getLabel());
        $this->assertSame('已取消', LogStatus::CANCELLED->getLabel());
    }
}
