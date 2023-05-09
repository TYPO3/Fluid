<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The mother of all test cases.
 *
 * Don't subclass this test case but rather choose a more specialized base test case,
 * such as UnitTestCase or FunctionalTestCase
 */
abstract class BaseTestCase extends TestCase
{
    /**
     * Returns a mock object which allows for calling protected methods and access
     * of protected properties.
     *
     * @template T of object
     * @param class-string<T> $originalClassName Full qualified name of the original class
     * @return MockObject&AccessibleObjectInterface&T
     * @deprecated Unused. Will be removed.
     */
    protected function getAccessibleMock(
        string $originalClassName,
        ?array $methods = null,
        array $arguments = [],
        string $mockClassName = '',
        bool $callOriginalConstructor = true,
        bool $callOriginalClone = true,
        bool $callAutoload = true
    ) {
        $builder = $this->getMockBuilder($this->buildAccessibleProxy($originalClassName))
            ->setConstructorArgs($arguments)
            ->setMockClassName($mockClassName);
        if ($methods !== null) {
            $builder->onlyMethods($methods);
        }
        if (!$callAutoload) {
            $builder->disableAutoload();
        }
        if (!$callOriginalClone) {
            $builder->disableOriginalClone();
        }
        if (!$callOriginalConstructor) {
            $builder->disableOriginalConstructor();
        }
        return $builder->getMock();
    }

    /**
     * @deprecated Unused. Will be removed.
     */
    protected static function assertAttributeEquals($expected, string $actualAttributeName, $actualClassOrObject): void
    {
        $reflection = new \ReflectionClass($actualClassOrObject);
        $attribute = $reflection->getProperty($actualAttributeName);
        $value = $attribute->getValue($actualClassOrObject);
        self::assertEquals($expected, $value);
    }

    /**
     * @deprecated Unused. Will be removed.
     */
    protected static function assertAttributeSame($expected, string $actualAttributeName, $actualClassOrObject): void
    {
        $reflection = new \ReflectionClass($actualClassOrObject);
        $attribute = $reflection->getProperty($actualAttributeName);
        $value = $attribute->getValue($actualClassOrObject);
        self::assertSame($expected, $value);
    }

    /**
     * @deprecated Unused. Will be removed.
     */
    protected static function assertAttributeContains($needle, string $haystackAttributeName, $haystackClassOrObject): void
    {
        $reflection = new \ReflectionClass($haystackClassOrObject);
        $attribute = $reflection->getProperty($haystackAttributeName);
        $value = $attribute->getValue($haystackClassOrObject);
        self::assertContains($needle, $value);
    }

    /**
     * @deprecated Unused. Will be removed.
     */
    protected static function assertAttributeNotEmpty(string $haystackAttributeName, $haystackClassOrObject): void
    {
        $reflection = new \ReflectionClass($haystackClassOrObject);
        $attribute = $reflection->getProperty($haystackAttributeName);
        $value = $attribute->getValue($haystackClassOrObject);
        self::assertNotEmpty($value);
    }

    /**
     * @deprecated Unused. Will be removed.
     */
    protected static function assertAttributeInstanceOf(string $expected, string $attributeName, $classOrObject): void
    {
        $reflection = new \ReflectionClass($classOrObject);
        $attribute = $reflection->getProperty($attributeName);
        $value = $attribute->getValue($classOrObject);
        self::assertInstanceOf($expected, $value);
    }

    /**
     * @deprecated Unused. Will be removed. Use createMock() or getMockBuilder() directly.
     */
    protected function getMock(
        string $originalClassName,
        ?array $methods = null,
        array $arguments = [],
        bool $callOriginalConstructor = true,
        bool $callOriginalClone = true,
        bool $callAutoload = true
    ) {
        $builder = $this->getMockBuilder($originalClassName);
        if ($methods !== null) {
            $builder->onlyMethods($methods);
        }
        $builder->setConstructorArgs($arguments);
        if (!$callAutoload) {
            $builder->disableAutoload();
        }
        if (!$callOriginalClone) {
            $builder->disableOriginalClone();
        }
        if (!$callOriginalConstructor) {
            $builder->disableOriginalConstructor();
        }
        return $builder->getMock();
    }

    /**
     * @deprecated Unused. Will be removed.
     */
    protected function getAccessibleMockForAbstractClass(
        string $originalClassName,
        array $arguments = [],
        string $mockClassName = '',
        bool $callOriginalConstructor = true,
        bool $callOriginalClone = true,
        bool $callAutoload = true
    ): object {
        return $this->getMockForAbstractClass($this->buildAccessibleProxy($originalClassName), $arguments, $mockClassName, $callOriginalConstructor, $callOriginalClone, $callAutoload);
    }

    /**
     * Creates a proxy class of the specified class which allows
     * for calling even protected methods and access of protected properties.
     *
     * @template T of object
     * @param class-string<T> $className Full qualified name of the original class
     * @return class-string<AccessibleObjectInterface&T> Full qualified name of the built class
     * @deprecated Remove together with consuming methods.
     */
    private function buildAccessibleProxy(string $className): string
    {
        $accessibleClassName = 'AccessibleTestProxy' . md5(uniqid((string)mt_rand(), true));
        $class = new \ReflectionClass($className);
        $abstractModifier = $class->isAbstract() ? 'abstract ' : '';
        eval('
			' . $abstractModifier . 'class ' . $accessibleClassName . ' extends ' . $className . ' implements ' . AccessibleObjectInterface::class . ' {
				public function _call(string $methodName, ...$methodArguments) {
				    return $this->$methodName(...$methodArguments);
				}
				public function _set(string $propertyName, $value): void {
					$this->$propertyName = $value;
				}
				public function _get(string $propertyName) {
					return $this->$propertyName;
				}
			}
		');
        return $accessibleClassName;
    }
}
