<?php

namespace MarketingPlanBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TestArrayCollection::class)]
final class TestArrayCollectionTest extends TestCase
{
    public function testMaxWithEmptyCollectionReturnsZero(): void
    {        // Arrange
        $collection = new TestArrayCollection();

        // Act
        $result = $collection->max();

        // Assert
        $this->assertEquals(0, $result);
    }

    public function testMaxWithValuesReturnsMaxValue(): void
    {        // Arrange
        $collection = new TestArrayCollection([1, 5, 3, 9, 2]);

        // Act
        $result = $collection->max();

        // Assert
        $this->assertEquals(9, $result);
    }
}
