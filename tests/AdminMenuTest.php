<?php

namespace MarketingPlanBundle\Tests;

use Knp\Menu\ItemInterface;
use MarketingPlanBundle\AdminMenu;
use MarketingPlanBundle\Entity\Task;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;

class AdminMenuTest extends TestCase
{
    public function testInvoke_addsExpectedChildToMenu(): void
    {
        // Arrange
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $linkGenerator->method('getCurdListPage')
            ->with(Task::class)
            ->willReturn('/admin/marketing-plan/task');

        $adminMenu = new AdminMenu($linkGenerator);

        $menuItem = $this->createMock(ItemInterface::class);
        $childMenuItem = $this->createMock(ItemInterface::class);

        $menuItem->expects($this->once())
            ->method('addChild')
            ->with('自动化流程')
            ->willReturn($childMenuItem);

        $childMenuItem->expects($this->once())
            ->method('setUri')
            ->with('/admin/marketing-plan/task')
            ->willReturn($childMenuItem);

        // Act
        $adminMenu->__invoke($menuItem);

        // No explicit assertions needed as the expectations on the mocks serve as assertions
    }
} 