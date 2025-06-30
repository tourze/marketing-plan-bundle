<?php

namespace MarketingPlanBundle\Tests\Entity;

use MarketingPlanBundle\Entity\Log;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\LogStatus;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testToString_returnsFormattedString(): void
    {
        // Arrange
        $log = new Log();
        $log->setStatus(LogStatus::IN_PROGRESS);

        // Act
        $result = (string) $log;

        // Assert
        $this->assertStringContainsString('in_progress', $result);
    }

    public function testSettersAndGetters(): void
    {
        // Arrange
        $log = new Log();
        $task = $this->createMock(Task::class);
        $userId = 'user123';
        $status = LogStatus::COMPLETED;
        $context = ['key' => 'value'];
        $completeTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $failureReason = '错误原因';
        $progressData = ['progress' => 50];

        // Act
        $log->setTask($task)
            ->setUserId($userId)
            ->setStatus($status)
            ->setContext($context)
            ->setCompleteTime($completeTime)
            ->setFailureReason($failureReason)
            ->setProgressData($progressData);

        // Assert
        $this->assertSame($task, $log->getTask());
        $this->assertEquals($userId, $log->getUserId());
        $this->assertEquals($status, $log->getStatus());
        $this->assertEquals($context, $log->getContext());
        $this->assertEquals($completeTime, $log->getCompleteTime());
        $this->assertEquals($failureReason, $log->getFailureReason());
        $this->assertEquals($progressData, $log->getProgressData());
    }
}
