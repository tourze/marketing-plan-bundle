<?php

namespace MarketingPlanBundle\Tests\Controller\Admin;

use MarketingPlanBundle\Controller\Admin\NodeDelayCrudController;
use MarketingPlanBundle\Entity\NodeDelay;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NodeDelayCrudControllerTest extends WebTestCase
{
    public function testGetEntityFqcn(): void
    {
        $result = NodeDelayCrudController::getEntityFqcn();
        $this->assertSame(NodeDelay::class, $result);
    }

    public function testConfigureFields(): void
    {
        $controller = new NodeDelayCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));
        
        $this->assertCount(3, $fields);
        
        $fieldNames = array_map(fn($field) => $field->getAsDto()->getProperty(), $fields);
        $this->assertContains('type', $fieldNames);
        $this->assertContains('value', $fieldNames);
        $this->assertContains('specificTime', $fieldNames);
    }
}
