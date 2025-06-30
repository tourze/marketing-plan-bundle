<?php

namespace MarketingPlanBundle\Tests\Unit\Exception;

use MarketingPlanBundle\Exception\UserProgressException;
use MarketingPlanBundle\Exception\MarketingPlanException;
use PHPUnit\Framework\TestCase;

class UserProgressExceptionTest extends TestCase
{
    public function testExceptionCreation(): void
    {
        // Arrange
        $message = '用户进度更新失败';
        $code = 4001;
        $previousException = new \Exception('前一个异常');

        // Act
        $exception = new UserProgressException($message, $code, $previousException);

        // Assert
        $this->assertInstanceOf(UserProgressException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function testExceptionWithDefaultParameters(): void
    {
        // Act
        $exception = new UserProgressException();

        // Assert
        $this->assertInstanceOf(UserProgressException::class, $exception);
        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionWithMessageOnly(): void
    {
        // Arrange
        $message = '用户进度数据无效';

        // Act
        $exception = new UserProgressException($message);

        // Assert
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testInheritance(): void
    {
        // Arrange & Act
        $exception = new UserProgressException();

        // Assert
        $this->assertInstanceOf(MarketingPlanException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
    }

    public function testExceptionCanBeThrown(): void
    {
        // Assert
        $this->expectException(UserProgressException::class);
        $this->expectExceptionMessage('用户进度状态冲突');
        $this->expectExceptionCode(409);

        // Act
        throw new UserProgressException('用户进度状态冲突', 409);
    }
}