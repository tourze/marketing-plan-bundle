<?php

namespace MarketingPlanBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * 自定义ArrayCollection，添加max方法
 */
class TestArrayCollection extends ArrayCollection
{
    public function max()
    {
        if ($this->isEmpty()) {
            return 0;
        }
        return max($this->toArray());
    }
}

class TestArrayCollectionTest extends TestCase
{
    public function testMax_withEmptyCollection_returnsZero(): void
    {
        // Arrange
        $collection = new TestArrayCollection();

        // Act
        $result = $collection->max();

        // Assert
        $this->assertEquals(0, $result);
    }

    public function testMax_withValues_returnsMaxValue(): void
    {
        // Arrange
        $collection = new TestArrayCollection([1, 5, 3, 9, 2]);

        // Act
        $result = $collection->max();

        // Assert
        $this->assertEquals(9, $result);
    }
} 