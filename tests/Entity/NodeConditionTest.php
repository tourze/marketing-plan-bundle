<?php

namespace MarketingPlanBundle\Tests\Entity;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeCondition;
use MarketingPlanBundle\Enum\ConditionOperator;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;

/**
 * @internal
 */
#[CoversClass(NodeCondition::class)]
final class NodeConditionTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new NodeCondition();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setResource($resourceConfig);

        yield 'name' => ['name', '测试条件'];
        yield 'field' => ['field', 'user.age'];
        yield 'operator' => ['operator', ConditionOperator::GREATER_THAN];
        yield 'value' => ['value', '18'];
        yield 'node' => ['node', $node];
    }

    public function testToStringReturnsFormattedString(): void
    {
        // Arrange
        $condition = new NodeCondition();
        $condition->setName('年龄条件');
        $condition->setField('user.age');
        $condition->setOperator(ConditionOperator::GREATER_THAN);
        $condition->setValue('18');

        // Act
        $result = (string) $condition;

        // Assert
        $this->assertStringContainsString('年龄条件 (user.age gt 18)', $result);
    }
}
