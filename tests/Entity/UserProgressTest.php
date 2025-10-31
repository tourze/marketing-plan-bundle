<?php

namespace MarketingPlanBundle\Tests\Entity;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeStage;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Entity\UserProgress;
use MarketingPlanBundle\Enum\ProgressStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;

/**
 * @internal
 */
#[CoversClass(UserProgress::class)]
final class UserProgressTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new UserProgress();
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

        yield 'task' => ['task', new Task()];
        yield 'userId' => ['userId', 'user123'];
        yield 'currentNode' => ['currentNode', $node];
        yield 'status' => ['status', ProgressStatus::PENDING];
        yield 'startTime' => ['startTime', new \DateTimeImmutable('2024-01-01 10:00:00')];
        yield 'finishTime' => ['finishTime', new \DateTimeImmutable('2024-01-31 23:59:59')];
    }

    public function testToStringReturnsFormattedString(): void
    {
        // Arrange
        $progress = new UserProgress();
        $progress->setUserId('user123');
        $progress->setStatus(ProgressStatus::PENDING);

        // Act
        $result = (string) $progress;

        // Assert
        $this->assertStringContainsString('UserProgress #0 - User: user123 - Status: pending', $result);
    }

    public function testNodeStageMethods(): void
    {
        // Arrange
        $progress = new UserProgress();
        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setResource($resourceConfig);
        $stage = new NodeStage();

        // Act & Assert - Initially no stage
        $this->assertNull($progress->getNodeStage($node));
        $this->assertFalse($progress->isNodeTouched($node));
        $this->assertFalse($progress->isNodeActivated($node));
        $this->assertFalse($progress->isNodeDropped($node));

        // Act & Assert - Add stage
        $stage->setNode($node);
        $progress->addStage($stage);
        $this->assertSame($stage, $progress->getNodeStage($node));
    }
}
