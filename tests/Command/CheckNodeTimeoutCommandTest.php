<?php

namespace MarketingPlanBundle\Tests\Command;

use MarketingPlanBundle\Command\CheckNodeTimeoutCommand;
use MarketingPlanBundle\Repository\NodeRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(CheckNodeTimeoutCommand::class)]
#[RunTestsInSeparateProcesses]
final class CheckNodeTimeoutCommandTest extends AbstractCommandTestCase
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

        // Mock NodeRepository to return empty array, avoiding database dependency
        $mockNodeRepository = $this->createMock(NodeRepository::class);
        $mockNodeRepository->method('findAll')->willReturn([]);

        // Replace the service in the container after kernel boot
        self::getContainer()->set(NodeRepository::class, $mockNodeRepository);

        $application = new Application($kernel);
        $command = $application->find('marketing-plan:check-node-timeout');
        $this->commandTester = new CommandTester($command);
    }

    public function testCommandCanBeInstantiated(): void
    {
        $command = self::getContainer()->get(CheckNodeTimeoutCommand::class);

        $this->assertInstanceOf(CheckNodeTimeoutCommand::class, $command);
        $this->assertEquals('marketing-plan:check-node-timeout', CheckNodeTimeoutCommand::NAME);
    }

    public function testCommandExecution(): void
    {
        // 命令在没有数据时应该正常执行（不抛出异常）
        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
    }
}
