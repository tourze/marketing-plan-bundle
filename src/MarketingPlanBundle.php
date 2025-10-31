<?php

namespace MarketingPlanBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineResolveTargetEntityBundle\DoctrineResolveTargetEntityBundle;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\DoctrineUserBundle\DoctrineUserBundle;
use Tourze\ResourceManageBundle\ResourceManageBundle;
use UserTagBundle\UserTagBundle;

class MarketingPlanBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            DoctrineResolveTargetEntityBundle::class => ['all' => true],
            DoctrineTimestampBundle::class => ['all' => true],
            DoctrineUserBundle::class => ['all' => true],
            ResourceManageBundle::class => ['all' => true],
            UserTagBundle::class => ['all' => true],
        ];
    }
}
