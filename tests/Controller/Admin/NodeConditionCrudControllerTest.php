<?php

namespace MarketingPlanBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use MarketingPlanBundle\Controller\Admin\NodeConditionCrudController;
use MarketingPlanBundle\Entity\NodeCondition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(NodeConditionCrudController::class)]
#[RunTestsInSeparateProcesses]
final class NodeConditionCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return NodeConditionCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(NodeConditionCrudController::class);
    }

    /**
     * 提供索引页面表头
     *
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'name' => ['名称'];
        yield 'field' => ['字段'];
        yield 'operator' => ['操作符'];
        yield 'value' => ['值'];
    }

    /**
     * 提供新建页面字段
     *
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'name' => ['name'];
        yield 'field' => ['field'];
        // value是TextareaField，测试基类只支持input字段检查，因此不包含在测试中
    }

    /**
     * 提供编辑页面字段
     *
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'name' => ['name'];
        yield 'field' => ['field'];
        // value是TextareaField，测试基类只支持input字段检查，因此不包含在测试中
    }

    public function testConfigureFields(): void
    {
        $controller = new NodeConditionCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
        $this->assertCount(4, $fields);
    }
}
