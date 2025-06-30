<?php

namespace MarketingPlanBundle\Tests\Controller\Admin;

use MarketingPlanBundle\Controller\Admin\NodeCrudController;
use MarketingPlanBundle\Entity\Node;
use PHPUnit\Framework\TestCase;
use Tourze\ResourceManageBundle\Service\ResourceManager;

class NodeCrudControllerTest extends TestCase
{
    private ResourceManager $resourceManager;

    protected function setUp(): void
    {
        $this->resourceManager = $this->createMock(ResourceManager::class);
        $this->resourceManager->method('genSelectData')->willReturn([
            ['label' => 'Test Resource', 'value' => 'test']
        ]);
    }

    public function testGetEntityFqcn(): void
    {
        $result = NodeCrudController::getEntityFqcn();
        $this->assertSame(Node::class, $result);
    }

    public function testConfigureCrud(): void
    {
        $controller = new NodeCrudController($this->resourceManager);
        $crud = $this->createMock(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::class);
        
        $crud->expects($this->once())->method('setEntityLabelInSingular')->with('节点')->willReturnSelf();
        $crud->expects($this->once())->method('setEntityLabelInPlural')->with('节点列表')->willReturnSelf();
        
        $result = $controller->configureCrud($crud);
        $this->assertInstanceOf(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::class, $result);
    }

    public function testConfigureFields(): void
    {
        $controller = new NodeCrudController($this->resourceManager);
        $fields = iterator_to_array($controller->configureFields('index'));
        
        $this->assertGreaterThan(0, count($fields));
    }

    public function testConfigureActions(): void
    {
        $controller = new NodeCrudController($this->resourceManager);
        $actions = \EasyCorp\Bundle\EasyAdminBundle\Config\Actions::new()
            ->add(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::PAGE_INDEX, \EasyCorp\Bundle\EasyAdminBundle\Config\Action::NEW)
            ->add(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::PAGE_INDEX, \EasyCorp\Bundle\EasyAdminBundle\Config\Action::EDIT)
            ->add(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::PAGE_INDEX, \EasyCorp\Bundle\EasyAdminBundle\Config\Action::DELETE);
        
        $result = $controller->configureActions($actions);
        $this->assertInstanceOf(\EasyCorp\Bundle\EasyAdminBundle\Config\Actions::class, $result);
    }
}
