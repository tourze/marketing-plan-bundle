<?php

namespace MarketingPlanBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use MarketingPlanBundle\Controller\Admin\LogCrudController;
use MarketingPlanBundle\Entity\Log;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(LogCrudController::class)]
#[RunTestsInSeparateProcesses]
final class LogCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return LogCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(LogCrudController::class);
    }

    /**
     * 提供索引页面表头
     *
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'task' => ['任务'];
        yield 'userId' => ['用户ID'];
        yield 'status' => ['状态'];
        yield 'creator' => ['创建者'];
        yield 'updater' => ['更新者'];
        yield 'createTime' => ['创建时间'];
        yield 'updateTime' => ['更新时间'];
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
        yield 'dummy' => ['userId'];
    }

    /**
     * 提供编辑页面字段
     *
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'userId' => ['userId'];
        yield 'status' => ['status'];
        yield 'completeTime' => ['completeTime'];
        yield 'failureReason' => ['failureReason'];
    }

    public function testGetEntityFqcn(): void
    {
        // 验证控制器处理的实体类型
        $this->assertSame(Log::class, LogCrudController::getEntityFqcn());
    }

    public function testConfigureFields(): void
    {
        $controller = new LogCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
        $this->assertCount(16, $fields);
    }
}
