<?php

namespace MarketingPlanBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * 自定义ArrayCollection，添加max方法（专门处理整数）
 * @template TKey of array-key
 * @extends ArrayCollection<TKey, int>
 */
class TestArrayCollection extends ArrayCollection
{
    public function max(): int
    {
        if ($this->isEmpty()) {
            return 0;
        }

        $array = $this->toArray();
        if (0 === count($array)) {
            return 0;
        }

        return max($array);
    }
}
