<?php

declare(strict_types=1);

namespace MarketingPlanBundle\Tests;

use MarketingPlanBundle\MarketingPlanBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(MarketingPlanBundle::class)]
#[RunTestsInSeparateProcesses]
final class MarketingPlanBundleTest extends AbstractBundleTestCase
{
}
