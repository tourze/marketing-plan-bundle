<?php

namespace MarketingPlanBundle\Tests\Unit\Exception;

use MarketingPlanBundle\Exception\MarketingPlanException;
use PHPUnit\Framework\TestCase;

class MarketingPlanExceptionTest extends TestCase
{
    public function testIsAbstractClass(): void
    {
        // Arrange
        $reflection = new \ReflectionClass(MarketingPlanException::class);

        // Assert
        $this->assertTrue($reflection->isAbstract());
    }

    public function testInheritance(): void
    {
        // Arrange
        $reflection = new \ReflectionClass(MarketingPlanException::class);

        // Assert
        $this->assertTrue($reflection->isSubclassOf(\Exception::class));
        $this->assertTrue($reflection->implementsInterface(\Throwable::class));
    }

    public function testCannotBeInstantiatedDirectly(): void
    {
        // Arrange
        $reflection = new \ReflectionClass(MarketingPlanException::class);

        // Assert - 抽象类不能直接实例化
        $this->assertTrue($reflection->isAbstract());
        
        // 使用反射验证无法实例化
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot instantiate abstract class');
        $reflection->newInstance();
    }

    public function testConcreteImplementationWorks(): void
    {
        // Arrange - 创建一个具体实现来测试基础功能
        $concreteException = new class('测试消息', 123) extends MarketingPlanException {};

        // Assert
        $this->assertInstanceOf(MarketingPlanException::class, $concreteException);
        $this->assertInstanceOf(\Exception::class, $concreteException);
        $this->assertEquals('测试消息', $concreteException->getMessage());
        $this->assertEquals(123, $concreteException->getCode());
    }

    public function testConcreteImplementationCanBeThrown(): void
    {
        // Arrange
        $concreteException = new class('可抛出的异常') extends MarketingPlanException {};

        // Assert
        $this->expectException(MarketingPlanException::class);
        $this->expectExceptionMessage('可抛出的异常');

        // Act
        throw $concreteException;
    }
}