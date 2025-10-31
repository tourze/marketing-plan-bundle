<?php

namespace MarketingPlanBundle\Tests\Entity;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeStage;
use MarketingPlanBundle\Entity\UserProgress;
use MarketingPlanBundle\Enum\DropReason;
use MarketingPlanBundle\Enum\NodeStageStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;

/**
 * @internal
 */
#[CoversClass(NodeStage::class)]
final class NodeStageTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new NodeStage();
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

        yield 'userProgress' => ['userProgress', new UserProgress()];
        yield 'node' => ['node', $node];
        yield 'status' => ['status', NodeStageStatus::PENDING];
        yield 'reachTime' => ['reachTime', new \DateTimeImmutable('2024-01-01 10:00:00')];
        yield 'touchTime' => ['touchTime', new \DateTimeImmutable('2024-01-01 10:30:00')];
        yield 'activeTime' => ['activeTime', new \DateTimeImmutable('2024-01-01 11:00:00')];
        yield 'finishTime' => ['finishTime', new \DateTimeImmutable('2024-01-01 12:00:00')];
        yield 'dropTime' => ['dropTime', new \DateTimeImmutable('2024-01-01 13:00:00')];
        yield 'dropReason' => ['dropReason', DropReason::TIMEOUT];
    }

    public function testToStringReturnsFormattedString(): void
    {
        // Arrange
        $stage = new NodeStage();
        $stage->setStatus(NodeStageStatus::PENDING);

        // Act
        $result = (string) $stage;

        // Assert
        $this->assertStringContainsString('NodeStage #0 - pending', $result);
    }

    public function testStatusMethods(): void
    {
        // Arrange
        $stage = new NodeStage();

        // Act & Assert - Initially not touched
        $this->assertFalse($stage->isTouched());
        $this->assertFalse($stage->isActivated());
        $this->assertFalse($stage->isDropped());

        // Act & Assert - Set touch time
        $stage->setTouchTime(new \DateTimeImmutable('2024-01-01 10:00:00'));
        $this->assertTrue($stage->isTouched());
        $this->assertFalse($stage->isActivated());
        $this->assertFalse($stage->isDropped());

        // Act & Assert - Set active time
        $stage->setActiveTime(new \DateTimeImmutable('2024-01-01 11:00:00'));
        $this->assertTrue($stage->isTouched());
        $this->assertTrue($stage->isActivated());
        $this->assertFalse($stage->isDropped());

        // Act & Assert - Set drop time
        $stage->setDropTime(new \DateTimeImmutable('2024-01-01 12:00:00'));
        $this->assertTrue($stage->isTouched());
        $this->assertTrue($stage->isActivated());
        $this->assertTrue($stage->isDropped());
    }
}
