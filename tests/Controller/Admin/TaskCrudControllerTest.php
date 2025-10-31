<?php

namespace MarketingPlanBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use MarketingPlanBundle\Controller\Admin\TaskCrudController;
use MarketingPlanBundle\Entity\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(TaskCrudController::class)]
#[RunTestsInSeparateProcesses]
final class TaskCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return TaskCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(TaskCrudController::class);
    }

    /**
     * 提供索引页面表头
     *
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'title' => ['标题'];
        yield 'description' => ['描述'];
        yield 'targetGroup' => ['人群'];
        yield 'status' => ['状态'];
        yield 'startTime' => ['开始时间'];
        yield 'endTime' => ['结束时间'];
        yield 'createTime' => ['创建时间'];
        yield 'updateTime' => ['更新时间'];
    }

    /**
     * 提供新建页面字段
     *
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'title' => ['title'];
        // description是TextareaField，测试基类只支持input字段检查，因此不包含在测试中
    }

    /**
     * 提供编辑页面字段
     *
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'title' => ['title'];
        // description是TextareaField，测试基类只支持input字段检查，因此不包含在测试中
    }

    /**
     * 重写父类的testNewPageFieldsProviderHasData方法，适配Task实体的字段
     */
    public function testGetEntityFqcn(): void
    {
        // 验证控制器处理的实体类型
        $this->assertSame(Task::class, TaskCrudController::getEntityFqcn());
    }

    public function testConfigureFields(): void
    {
        $controller = new TaskCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
        $this->assertCount(8, $fields);
    }
}
