<?php

namespace MarketingPlanBundle\Tests\Unit\Enum;

use MarketingPlanBundle\Enum\DelayType;
use PHPUnit\Framework\TestCase;

class DelayTypeTest extends TestCase
{
    public function testCases(): void
    {
        $cases = DelayType::cases();
        $this->assertCount(4, $cases);
        
        $expectedCases = [
            DelayType::MINUTES,
            DelayType::HOURS,
            DelayType::DAYS,
            DelayType::SPECIFIC_TIME,
        ];
        
        foreach ($expectedCases as $expectedCase) {
            $this->assertContains($expectedCase, $cases);
        }
    }

    public function testValues(): void
    {
        $this->assertSame('minutes', DelayType::MINUTES->value);
        $this->assertSame('hours', DelayType::HOURS->value);
        $this->assertSame('days', DelayType::DAYS->value);
        $this->assertSame('specific_time', DelayType::SPECIFIC_TIME->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('分钟', DelayType::MINUTES->getLabel());
        $this->assertSame('小时', DelayType::HOURS->getLabel());
        $this->assertSame('天', DelayType::DAYS->getLabel());
        $this->assertSame('具体时间', DelayType::SPECIFIC_TIME->getLabel());
    }
}
