<?php

namespace MarketingPlanBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class MarketingPlanExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
