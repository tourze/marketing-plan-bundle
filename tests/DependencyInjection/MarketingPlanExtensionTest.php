<?php

namespace MarketingPlanBundle\Tests\DependencyInjection;

use MarketingPlanBundle\DependencyInjection\MarketingPlanExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MarketingPlanExtensionTest extends TestCase
{
    public function testLoad_registersServices(): void
    {
        // Arrange
        $extension = new MarketingPlanExtension();
        $container = new ContainerBuilder();

        // Act
        $extension->load([], $container);

        // Assert
        $this->assertTrue($container->hasDefinition('MarketingPlanBundle\Service\NodeService'));
        $this->assertTrue($container->hasDefinition('MarketingPlanBundle\Service\TaskService'));
        $this->assertTrue($container->hasDefinition('MarketingPlanBundle\Service\UserProgressService'));
    }
} 