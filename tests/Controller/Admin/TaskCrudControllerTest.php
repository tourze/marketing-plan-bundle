<?php

namespace MarketingPlanBundle\Tests\Controller\Admin;

use MarketingPlanBundle\Controller\Admin\TaskCrudController;
use MarketingPlanBundle\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        $result = TaskCrudController::getEntityFqcn();
        $this->assertSame(Task::class, $result);
    }

    public function testConfigureCrud(): void
    {
        $controller = new TaskCrudController();
        $crud = $this->createMock(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::class);
        
        $crud->expects($this->once())->method('setEntityLabelInSingular')->with('任务')->willReturnSelf();
        $crud->expects($this->once())->method('setEntityLabelInPlural')->with('任务列表')->willReturnSelf();
        
        $result = $controller->configureCrud($crud);
        $this->assertInstanceOf(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::class, $result);
    }

    public function testConfigureFields(): void
    {
        $controller = new TaskCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));
        
        $this->assertGreaterThan(0, count($fields));
    }

    public function testConfigureActions(): void
    {
        $controller = new TaskCrudController();
        $actions = \EasyCorp\Bundle\EasyAdminBundle\Config\Actions::new()
            ->add(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::PAGE_INDEX, \EasyCorp\Bundle\EasyAdminBundle\Config\Action::NEW)
            ->add(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::PAGE_INDEX, \EasyCorp\Bundle\EasyAdminBundle\Config\Action::EDIT)
            ->add(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::PAGE_INDEX, \EasyCorp\Bundle\EasyAdminBundle\Config\Action::DELETE);
        
        $result = $controller->configureActions($actions);
        $this->assertInstanceOf(\EasyCorp\Bundle\EasyAdminBundle\Config\Actions::class, $result);
    }

    public function testConfigureAssets(): void
    {
        $controller = new TaskCrudController();
        $assets = \EasyCorp\Bundle\EasyAdminBundle\Config\Assets::new();
        
        $result = $controller->configureAssets($assets);
        $this->assertInstanceOf(\EasyCorp\Bundle\EasyAdminBundle\Config\Assets::class, $result);
    }
}
