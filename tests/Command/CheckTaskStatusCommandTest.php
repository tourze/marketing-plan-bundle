<?php

namespace MarketingPlanBundle\Tests\Command;

use MarketingPlanBundle\Command\CheckTaskStatusCommand;
use MarketingPlanBundle\Repository\TaskRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(CheckTaskStatusCommand::class)]
#[RunTestsInSeparateProcesses]
final class CheckTaskStatusCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;
        $application = new Application($kernel);
        $command = $application->find('marketing-plan:check-task-status');
        $this->commandTester = new CommandTester($command);
    }

    public function testCommandCanBeInstantiated(): void
    {
        $command = self::getContainer()->get(CheckTaskStatusCommand::class);

        $this->assertInstanceOf(CheckTaskStatusCommand::class, $command);
        $this->assertEquals('marketing-plan:check-task-status', CheckTaskStatusCommand::NAME);
    }

    public function testCommandExecution(): void
    {
        $taskRepository = $this->createMock(TaskRepository::class);
        $taskRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([])
        ;

        self::getContainer()->set(TaskRepository::class, $taskRepository);

        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
