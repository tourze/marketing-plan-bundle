<?php

namespace MarketingPlanBundle\Tests\Command;

use MarketingPlanBundle\Command\CheckTaskStatusCommand;
use MarketingPlanBundle\Repository\TaskRepository;
use MarketingPlanBundle\Service\TaskService;
use PHPUnit\Framework\TestCase;

class CheckTaskStatusCommandTest extends TestCase
{
    public function testCommand_canBeInstantiated(): void
    {
        // Arrange
        $taskRepository = $this->createMock(TaskRepository::class);
        $taskService = $this->createMock(TaskService::class);

        // Act
        $command = new CheckTaskStatusCommand($taskRepository, $taskService);

        // Assert
        $this->assertInstanceOf(CheckTaskStatusCommand::class, $command);
        $this->assertEquals('marketing-plan:check-task-status', CheckTaskStatusCommand::NAME);
    }
}
