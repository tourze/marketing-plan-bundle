<?php

namespace MarketingPlanBundle\Tests\Unit\Enum;

use MarketingPlanBundle\Enum\ConditionOperator;
use PHPUnit\Framework\TestCase;

class ConditionOperatorTest extends TestCase
{
    public function testCases(): void
    {
        $cases = ConditionOperator::cases();
        $this->assertCount(10, $cases);
        
        $expectedCases = [
            ConditionOperator::EQUAL,
            ConditionOperator::NOT_EQUAL,
            ConditionOperator::GREATER_THAN,
            ConditionOperator::GREATER_THAN_OR_EQUAL,
            ConditionOperator::LESS_THAN,
            ConditionOperator::LESS_THAN_OR_EQUAL,
            ConditionOperator::CONTAINS,
            ConditionOperator::NOT_CONTAINS,
            ConditionOperator::IN,
            ConditionOperator::NOT_IN,
        ];
        
        foreach ($expectedCases as $expectedCase) {
            $this->assertContains($expectedCase, $cases);
        }
    }

    public function testValues(): void
    {
        $this->assertSame('eq', ConditionOperator::EQUAL->value);
        $this->assertSame('neq', ConditionOperator::NOT_EQUAL->value);
        $this->assertSame('gt', ConditionOperator::GREATER_THAN->value);
        $this->assertSame('gte', ConditionOperator::GREATER_THAN_OR_EQUAL->value);
        $this->assertSame('lt', ConditionOperator::LESS_THAN->value);
        $this->assertSame('lte', ConditionOperator::LESS_THAN_OR_EQUAL->value);
        $this->assertSame('contains', ConditionOperator::CONTAINS->value);
        $this->assertSame('not_contains', ConditionOperator::NOT_CONTAINS->value);
        $this->assertSame('in', ConditionOperator::IN->value);
        $this->assertSame('not_in', ConditionOperator::NOT_IN->value);
    }

    public function testGetLabel(): void
    {
        $this->assertSame('等于', ConditionOperator::EQUAL->getLabel());
        $this->assertSame('不等于', ConditionOperator::NOT_EQUAL->getLabel());
        $this->assertSame('大于', ConditionOperator::GREATER_THAN->getLabel());
        $this->assertSame('大于等于', ConditionOperator::GREATER_THAN_OR_EQUAL->getLabel());
        $this->assertSame('小于', ConditionOperator::LESS_THAN->getLabel());
        $this->assertSame('小于等于', ConditionOperator::LESS_THAN_OR_EQUAL->getLabel());
        $this->assertSame('包含', ConditionOperator::CONTAINS->getLabel());
        $this->assertSame('不包含', ConditionOperator::NOT_CONTAINS->getLabel());
        $this->assertSame('在范围内', ConditionOperator::IN->getLabel());
        $this->assertSame('不在范围内', ConditionOperator::NOT_IN->getLabel());
    }
}
