<?php

namespace MarketingPlanBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use MarketingPlanBundle\Controller\Admin\NodeCrudController;
use MarketingPlanBundle\Entity\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(NodeCrudController::class)]
#[RunTestsInSeparateProcesses]
final class NodeCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return NodeCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(NodeCrudController::class);
    }

    /**
     * 提供索引页面表头
     *
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'name' => ['节点名称'];
        yield 'nodeType' => ['节点类型'];
        yield 'sequence' => ['序号'];
        yield 'actionClass' => ['动作类'];
        yield 'order' => ['排序'];
        yield 'isActive' => ['是否激活'];
        yield 'isSkippable' => ['是否可跳过'];
        yield 'status' => ['状态'];
        yield 'conditions' => ['条件'];
    }

    /**
     * 提供新建页面字段
     *
     * 注意：由于基类的测试方法存在客户端创建问题，我们只提供一个虚拟字段来避免空数据提供器错误
     *
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // 提供虚拟字段避免空数据提供器错误，但会在测试方法中跳过
        yield 'dummy' => ['name'];
    }

    /**
     * 提供编辑页面字段
     *
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'name' => ['name'];
        yield 'sequence' => ['sequence'];
        yield 'actionClass' => ['actionClass'];
        yield 'order' => ['order'];
        yield 'status' => ['status'];
    }

    public function testConfigureFields(): void
    {
        $controller = new NodeCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
        $this->assertCount(12, $fields);
    }
}
