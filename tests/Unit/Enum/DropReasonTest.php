<?php

namespace MarketingPlanBundle\Tests\Unit\Enum;

use MarketingPlanBundle\Enum\DropReason;
use PHPUnit\Framework\TestCase;

class DropReasonTest extends TestCase
{
    public function testCases(): void
    {
        $cases = DropReason::cases();
        $this->assertCount(2, $cases);
        
        $expectedCases = [
            DropReason::TIMEOUT,
            DropReason::CONDITION_NOT_MET,
        ];
        
        foreach ($expectedCases as $expectedCase) {
            $this->assertContains($expectedCase, $cases);
        }
    }

    public function testValues(): void
    {
        $this->assertSame('timeout', DropReason::TIMEOUT->value);
        $this->assertSame('condition_not_met', DropReason::CONDITION_NOT_MET->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('超时未响应', DropReason::TIMEOUT->getLabel());
        $this->assertSame('不满足条件', DropReason::CONDITION_NOT_MET->getLabel());
    }
}
