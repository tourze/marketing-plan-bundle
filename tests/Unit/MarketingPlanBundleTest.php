<?php

namespace MarketingPlanBundle\Tests\Unit;

use MarketingPlanBundle\MarketingPlanBundle;
use PHPUnit\Framework\TestCase;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;

class MarketingPlanBundleTest extends TestCase
{
    public function testGetBundleDependencies_returnsExpectedDependencies(): void
    {
        // Arrange
        $expectedDependencies = [
            DoctrineTimestampBundle::class => ['all' => true],
        ];

        // Act
        $dependencies = MarketingPlanBundle::getBundleDependencies();

        // Assert
        $this->assertSame($expectedDependencies, $dependencies);
    }
} 