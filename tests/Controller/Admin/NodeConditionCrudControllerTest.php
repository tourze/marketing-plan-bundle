<?php

namespace MarketingPlanBundle\Tests\Controller\Admin;

use MarketingPlanBundle\Controller\Admin\NodeConditionCrudController;
use MarketingPlanBundle\Entity\NodeCondition;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NodeConditionCrudControllerTest extends WebTestCase
{
    public function testGetEntityFqcn(): void
    {
        $result = NodeConditionCrudController::getEntityFqcn();
        $this->assertSame(NodeCondition::class, $result);
    }

    public function testConfigureFields(): void
    {
        $controller = new NodeConditionCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));
        
        $this->assertCount(4, $fields);
        
        $fieldNames = array_map(fn($field) => $field->getAsDto()->getProperty(), $fields);
        $this->assertContains('name', $fieldNames);
        $this->assertContains('field', $fieldNames);
        $this->assertContains('operator', $fieldNames);
        $this->assertContains('value', $fieldNames);
    }
}
