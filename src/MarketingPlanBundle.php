<?php

namespace MarketingPlanBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class MarketingPlanBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \AntdCpBundle\AntdCpBundle::class => ['all' => true],
            \UserCrowdBundle\UserCrowdBundle::class => ['all' => true],
        ];
    }
}
