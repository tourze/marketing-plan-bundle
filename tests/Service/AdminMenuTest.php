<?php

declare(strict_types=1);

namespace MarketingPlanBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Service\AdminMenu;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses] final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    private LinkGeneratorInterface&MockObject $linkGenerator;

    private ItemInterface&MockObject $item;

    protected function onSetUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        self::getContainer()->set(LinkGeneratorInterface::class, $this->linkGenerator);
        $this->adminMenu = self::getService(AdminMenu::class);
        $this->item = $this->createMock(ItemInterface::class);
    }

    public function testServiceCreation(): void
    {
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);
    }

    public function testImplementsMenuProviderInterface(): void
    {
        $this->assertInstanceOf(MenuProviderInterface::class, $this->adminMenu);
    }

    public function testInvokeShouldBeCallable(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);
        $this->assertTrue($reflection->hasMethod('__invoke'));
    }

    public function testInvokeAddsExpectedChildToMenu(): void
    {
        $mainMenuItem = $this->createMock(ItemInterface::class);
        $taskMenuItem = $this->createMock(ItemInterface::class);

        // Mock multiple calls to getCurdListPage for all entities
        $this->linkGenerator->expects($this->exactly(7))
            ->method('getCurdListPage')
            ->willReturnMap([
                [Task::class, '/admin/marketing-plan/task'],
                ['MarketingPlanBundle\Entity\Node', '/admin/marketing-plan/node'],
                ['MarketingPlanBundle\Entity\UserProgress', '/admin/marketing-plan/user-progress'],
                ['MarketingPlanBundle\Entity\NodeStage', '/admin/marketing-plan/node-stage'],
                ['MarketingPlanBundle\Entity\NodeCondition', '/admin/marketing-plan/node-condition'],
                ['MarketingPlanBundle\Entity\NodeDelay', '/admin/marketing-plan/node-delay'],
                ['MarketingPlanBundle\Entity\Log', '/admin/marketing-plan/log'],
            ])
        ;

        // Main menu item should be added first
        $this->item->expects($this->once())
            ->method('addChild')
            ->with('营销计划管理', ['icon' => 'fas fa-rocket'])
            ->willReturn($mainMenuItem)
        ;

        // Mock the sub-menu calls
        $mainMenuItem->expects($this->exactly(7))
            ->method('addChild')
            ->willReturnCallback(function ($name, $options = []) use ($taskMenuItem) {
                return $taskMenuItem;
            })
        ;

        // Mock setUri calls for all menu items
        $taskMenuItem->expects($this->exactly(7))
            ->method('setUri')
        ;

        ($this->adminMenu)($this->item);
    }
}
