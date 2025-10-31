<?php

namespace MarketingPlanBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use MarketingPlanBundle\Controller\Admin\NodeDelayCrudController;
use MarketingPlanBundle\Entity\NodeDelay;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(NodeDelayCrudController::class)]
#[RunTestsInSeparateProcesses]
final class NodeDelayCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return NodeDelayCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(NodeDelayCrudController::class);
    }

    /**
     * 提供索引页面表头
     *
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'type' => ['类型'];
        yield 'value' => ['值'];
        yield 'specificTime' => ['具体时间'];
    }

    /**
     * 提供新建页面字段
     *
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'value' => ['value'];
        yield 'specificTime' => ['specificTime'];
    }

    /**
     * 提供编辑页面字段
     *
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'value' => ['value'];
        yield 'specificTime' => ['specificTime'];
    }

    public function testGetEntityFqcn(): void
    {
        // 验证控制器处理的实体类型
        $this->assertSame(NodeDelay::class, NodeDelayCrudController::getEntityFqcn());
    }

    public function testConfigureFields(): void
    {
        $controller = new NodeDelayCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
        $this->assertCount(3, $fields);
    }
}
