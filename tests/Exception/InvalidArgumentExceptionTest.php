<?php

namespace MarketingPlanBundle\Tests\Exception;

use MarketingPlanBundle\Exception\InvalidArgumentException;
use MarketingPlanBundle\Exception\MarketingPlanException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(InvalidArgumentException::class)]
final class InvalidArgumentExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCreation(): void
    {
        // Arrange
        $message = '无效参数';
        $code = 1001;
        $previousException = new \Exception('前一个异常');

        // Act
        $exception = new InvalidArgumentException($message, $code, $previousException);

        // Assert
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function testExceptionWithDefaultParameters(): void
    {
        // Act
        $exception = new InvalidArgumentException();

        // Assert
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionWithMessageOnly(): void
    {
        // Arrange
        $message = '参数类型错误';

        // Act
        $exception = new InvalidArgumentException($message);

        // Assert
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testInheritance(): void
    {
        // Arrange & Act
        $exception = new InvalidArgumentException();

        // Assert
        $this->assertInstanceOf(MarketingPlanException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
    }

    public function testExceptionCanBeThrown(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('参数值超出范围');
        $this->expectExceptionCode(400);

        // Act
        throw new InvalidArgumentException('参数值超出范围', 400);
    }
}
