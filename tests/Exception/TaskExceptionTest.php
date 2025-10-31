<?php

namespace MarketingPlanBundle\Tests\Exception;

use MarketingPlanBundle\Exception\MarketingPlanException;
use MarketingPlanBundle\Exception\TaskException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(TaskException::class)]
final class TaskExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCreation(): void
    {
        // Arrange
        $message = '任务执行失败';
        $code = 3001;
        $previousException = new \Exception('前一个异常');

        // Act
        $exception = new TaskException($message, $code, $previousException);

        // Assert
        $this->assertInstanceOf(TaskException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function testExceptionWithDefaultParameters(): void
    {
        // Act
        $exception = new TaskException();

        // Assert
        $this->assertInstanceOf(TaskException::class, $exception);
        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionWithMessageOnly(): void
    {
        // Arrange
        $message = '任务状态无效';

        // Act
        $exception = new TaskException($message);

        // Assert
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testInheritance(): void
    {
        // Arrange & Act
        $exception = new TaskException();

        // Assert
        $this->assertInstanceOf(MarketingPlanException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
    }

    public function testExceptionCanBeThrown(): void
    {
        // Assert
        $this->expectException(TaskException::class);
        $this->expectExceptionMessage('任务已超时');
        $this->expectExceptionCode(408);

        // Act
        throw new TaskException('任务已超时', 408);
    }
}
