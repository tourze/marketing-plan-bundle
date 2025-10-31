<?php

namespace MarketingPlanBundle\Tests\Entity;

use MarketingPlanBundle\Entity\Log;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\LogStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Log::class)]
final class LogTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Log();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'task' => ['task', new Task()];
        yield 'userId' => ['userId', 'user123'];
        yield 'status' => ['status', LogStatus::COMPLETED];
        yield 'context' => ['context', ['key' => 'value']];
        yield 'completeTime' => ['completeTime', new \DateTimeImmutable('2024-01-01 10:00:00')];
        yield 'failureReason' => ['failureReason', '错误原因'];
        yield 'progressData' => ['progressData', ['progress' => 50]];
    }

    public function testToStringReturnsFormattedString(): void
    {
        // Arrange
        $log = new Log();
        $log->setStatus(LogStatus::IN_PROGRESS);

        // Act
        $result = (string) $log;

        // Assert
        $this->assertStringContainsString('in_progress', $result);
    }
}
