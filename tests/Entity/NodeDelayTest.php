<?php

namespace MarketingPlanBundle\Tests\Entity;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeDelay;
use MarketingPlanBundle\Enum\DelayType;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;

/**
 * @internal
 */
#[CoversClass(NodeDelay::class)]
final class NodeDelayTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new NodeDelay();
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

        yield 'type' => ['type', DelayType::MINUTES];
        yield 'value' => ['value', 30];
        yield 'specificTime' => ['specificTime', new \DateTimeImmutable('2024-01-01 10:00:00')];
        yield 'node' => ['node', $node];
    }

    public function testToStringReturnsFormattedString(): void
    {
        // Arrange
        $delay = new NodeDelay();
        $delay->setType(DelayType::MINUTES);
        $delay->setValue(30);

        // Act
        $result = (string) $delay;

        // Assert
        $this->assertStringContainsString('30 分钟', $result);
    }

    public function testToStringWithSpecificTime(): void
    {
        // Arrange
        $delay = new NodeDelay();
        $delay->setType(DelayType::SPECIFIC_TIME);
        $delay->setSpecificTime(new \DateTimeImmutable('2024-01-01 10:00:00'));

        // Act
        $result = (string) $delay;

        // Assert
        $this->assertStringContainsString('延时至 2024-01-01 10:00:00', $result);
    }
}
