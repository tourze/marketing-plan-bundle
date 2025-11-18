<?php

namespace MarketingPlanBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use MarketingPlanBundle\Controller\Admin\NodeStageCrudController;
use MarketingPlanBundle\Entity\NodeStage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(NodeStageCrudController::class)]
#[RunTestsInSeparateProcesses]
final class NodeStageCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return NodeStageCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(NodeStageCrudController::class);
    }

    /**
     * 提供索引页面表头
     *
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'userProgress' => ['用户进度'];
        yield 'node' => ['节点'];
        yield 'status' => ['状态'];
        yield 'enterNodeTime' => ['进入节点时间'];
        yield 'reachTime' => ['触达时间'];
        yield 'activateTime' => ['激活时间'];
        yield 'completeTime' => ['完成时间'];
        yield 'dropTime' => ['流失时间'];
        yield 'createTime' => ['创建时间'];
        yield 'updateTime' => ['更新时间'];
    }

    /**
     * 提供编辑页面字段
     * 由于 NodeStage 是执行状态记录，禁用了编辑功能，提供占位数据以避免空数据提供器错误
     *
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'placeholder' => ['placeholder'];
    }

    /**
     * 重写父类的testNewPageFieldsProviderHasData方法，适配NodeStage实体的字段
     *
     * NodeStage 实体主要包含关联字段和枚举字段，这些字段在表单中表现为下拉框而非文本输入框，
     * 因此我们只验证配置的字段数量，不验证具体的字段渲染
     */

    /**
     * 提供新建页面字段
     *
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'userProgress' => ['userProgress'];
        yield 'node' => ['node'];
        yield 'status' => ['status'];
        yield 'dropReason' => ['dropReason'];
    }

    public function testConfigureFields(): void
    {
        $client = $this->createAuthenticatedClient();
        $controller = new NodeStageCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
        $this->assertCount(15, $fields);
    }
}
