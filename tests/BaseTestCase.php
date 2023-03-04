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

    public static function assertAttributeEquals($expected, string $actualAttributeName, $actualClassOrObject, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
    {
        self::assertEquals($expected, self::extractNonPublicAttribute($actualClassOrObject, $actualAttributeName));
    }

    public static function assertAttributeSame($expected, string $actualAttributeName, $actualClassOrObject, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
    {
        self::assertSame($expected, self::extractNonPublicAttribute($actualClassOrObject, $actualAttributeName));
    }

    public static function assertAttributeContains($needle, string $haystackAttributeName, $haystackClassOrObject, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
    {
        self::assertContains($needle, self::extractNonPublicAttribute($haystackClassOrObject, $haystackAttributeName));
    }

    public static function assertAttributeNotEmpty(string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
    {
        self::assertNotEmpty(self::extractNonPublicAttribute($haystackClassOrObject, $haystackAttributeName));
    }

    public static function assertAttributeInstanceOf(string $expected, string $attributeName, $classOrObject, string $message = ''): void
    {
        self::assertInstanceOf($expected, self::extractNonPublicAttribute($classOrObject, $attributeName));
    }

    private static function extractNonPublicAttribute($actualClassOrObject, string $actualAttributeName)
    {
        $reflection = new \ReflectionClass($actualClassOrObject);
        $attribute = $reflection->getProperty($actualAttributeName);
        $attribute->setAccessible(true);
        return $attribute->getValue($actualClassOrObject);
    }

    /**
     * Returns a mock object which allows for calling protected methods and access
     * of protected properties.
     *
     * @template T of object
     * @param class-string<T> $originalClassName Full qualified name of the original class
     * @return MockObject&T
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
     * Returns a mock object which allows for calling protected methods and access
     * of protected properties.
     *
     * @template T of object
     * @param class-string<T> $originalClassName Full qualified name of the original class
     * @return MockObject&AccessibleObjectInterface&T
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
