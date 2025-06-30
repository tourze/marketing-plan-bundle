<?php

namespace MarketingPlanBundle\Tests\Unit\Exception;

use MarketingPlanBundle\Exception\NodeException;
use MarketingPlanBundle\Exception\MarketingPlanException;
use PHPUnit\Framework\TestCase;

class NodeExceptionTest extends TestCase
{
    public function testExceptionCreation(): void
    {
        // Arrange
        $message = '节点操作失败';
        $code = 2001;
        $previousException = new \Exception('前一个异常');

        // Act
        $exception = new NodeException($message, $code, $previousException);

        // Assert
        $this->assertInstanceOf(NodeException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function testExceptionWithDefaultParameters(): void
    {
        // Act
        $exception = new NodeException();

        // Assert
        $this->assertInstanceOf(NodeException::class, $exception);
        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionWithMessageOnly(): void
    {
        // Arrange
        $message = '节点不存在';

        // Act
        $exception = new NodeException($message);

        // Assert
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testInheritance(): void
    {
        // Arrange & Act
        $exception = new NodeException();

        // Assert
        $this->assertInstanceOf(MarketingPlanException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
    }

    public function testExceptionCanBeThrown(): void
    {
        // Assert
        $this->expectException(NodeException::class);
        $this->expectExceptionMessage('节点状态异常');
        $this->expectExceptionCode(404);

        // Act
        throw new NodeException('节点状态异常', 404);
    }
}