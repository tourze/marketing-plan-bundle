<?php

namespace MarketingPlanBundle\Tests\Command;

use MarketingPlanBundle\Command\CheckNodeTimeoutCommand;
use MarketingPlanBundle\Repository\NodeRepository;
use MarketingPlanBundle\Service\UserProgressService;
use PHPUnit\Framework\TestCase;

class CheckNodeTimeoutCommandTest extends TestCase
{
    public function testCommand_canBeInstantiated(): void
    {
        // Arrange
        $nodeRepository = $this->createMock(NodeRepository::class);
        $userProgressService = $this->createMock(UserProgressService::class);

        // Act
        $command = new CheckNodeTimeoutCommand($nodeRepository, $userProgressService);

        // Assert
        $this->assertInstanceOf(CheckNodeTimeoutCommand::class, $command);
        $this->assertEquals('marketing-plan:check-node-timeout', CheckNodeTimeoutCommand::NAME);
    }
}
