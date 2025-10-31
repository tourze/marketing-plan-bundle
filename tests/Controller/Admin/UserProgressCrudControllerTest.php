<?php

namespace MarketingPlanBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use MarketingPlanBundle\Controller\Admin\UserProgressCrudController;
use MarketingPlanBundle\Entity\UserProgress;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(UserProgressCrudController::class)]
#[RunTestsInSeparateProcesses]
final class UserProgressCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return UserProgressCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(UserProgressCrudController::class);
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
        yield 'currentNode' => ['当前节点'];
        yield 'status' => ['状态'];
        yield 'startTime' => ['开始时间'];
        yield 'completeTime' => ['完成时间'];
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
        yield 'userId' => ['userId'];
    }

    /**
     * 提供编辑页面字段
     *
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'userId' => ['userId'];
    }

    /**
     * 重写父类的testNewPageFieldsProviderHasData方法，适配UserProgress实体的字段
     */
    public function testGetEntityFqcn(): void
    {
        // 验证控制器处理的实体类型
        $this->assertSame(UserProgress::class, UserProgressCrudController::getEntityFqcn());
    }

    public function testConfigureFields(): void
    {
        $controller = new UserProgressCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
        $this->assertCount(14, $fields);
    }
}
