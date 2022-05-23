<?php

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
 *
 * @api
 */
abstract class BaseTestCase extends TestCase
{

    /**
     * Enable or disable the backup and restoration of static attributes.
     * @var bool
     */
    protected $backupStaticAttributes = false;

    /**
     * Returns a mock object which allows for calling protected methods and access
     * of protected properties.
     *
     * @template T of object
     * @param class-string<T> $originalClassName Full qualified name of the original class
     * @param array $methods
     * @param array $arguments
     * @param string $mockClassName
     * @param bool $callOriginalConstructor
     * @param bool $callOriginalClone
     * @param bool $callAutoload
     * @return MockObject&AccessibleObjectInterface&T
     * @api
     */
    protected function getAccessibleMock($originalClassName, $methods = [], array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true)
    {
        $builder = $this->getMockBuilder($this->buildAccessibleProxy($originalClassName))->setMethods($methods)->setConstructorArgs($arguments)->setMockClassName($mockClassName);
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
        self::assertEquals($expected, static::extractNonPublicAttribute($actualClassOrObject, $actualAttributeName));
    }

    public static function assertAttributeSame($expected, string $actualAttributeName, $actualClassOrObject, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
    {
        self::assertSame($expected, static::extractNonPublicAttribute($actualClassOrObject, $actualAttributeName));
    }

    public static function assertAttributeContains($needle, string $haystackAttributeName, $haystackClassOrObject, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
    {
        self::assertContains($needle, static::extractNonPublicAttribute($haystackClassOrObject, $haystackAttributeName));
    }

    public static function assertAttributeNotEmpty(string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
    {
        self::assertNotEmpty(static::extractNonPublicAttribute($haystackClassOrObject, $haystackAttributeName));
    }

    public static function assertAttributeInstanceOf(string $expected, string $attributeName, $classOrObject, string $message = ''): void
    {
        self::assertInstanceOf($expected, static::extractNonPublicAttribute($classOrObject, $attributeName));
    }

    protected static function extractNonPublicAttribute($actualClassOrObject, string $actualAttributeName)
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
     * @param array $methods
     * @param array $arguments
     * @param bool $callOriginalConstructor
     * @param bool $callOriginalClone
     * @param bool $callAutoload
     * @return MockObject&T
     * @api
     */
    protected function getMock($originalClassName, $methods = [], array $arguments = [], $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true)
    {
        $builder = $this->getMockBuilder($originalClassName)->setMethods($methods)->setConstructorArgs($arguments);
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
     * @param array $arguments
     * @param string $mockClassName
     * @param bool $callOriginalConstructor
     * @param bool $callOriginalClone
     * @param bool $callAutoload
     * @return MockObject&AccessibleObjectInterface&T
     * @api
     */
    protected function getAccessibleMockForAbstractClass($originalClassName, array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true)
    {
        return $this->getMockForAbstractClass($this->buildAccessibleProxy($originalClassName), $arguments, $mockClassName, $callOriginalConstructor, $callOriginalClone, $callAutoload);
    }

    /**
     * Creates a proxy class of the specified class which allows
     * for calling even protected methods and access of protected properties.
     *
     * @template T of object
     * @param class-string<T> $className Full qualified name of the original class
     * @return class-string<AccessibleObjectInterface&T> Full qualified name of the built class
     * @api
     */
    protected function buildAccessibleProxy($className)
    {
        $accessibleClassName = 'AccessibleTestProxy' . md5(uniqid(mt_rand(), true));
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

    protected function setExpectedException(string $class = \Exception::class, string $message = '', int $code = 0)
    {
        if ($class) {
            $this->expectException($class);
        }
        if ($message) {
            $this->expectExceptionMessage($message);
        }
        if ($code) {
            $this->expectExceptionCode($code);
        }
    }

    /**
     * Injects $dependency into property $name of $target
     *
     * This is a convenience method for setting a protected or private property in
     * a test subject for the purpose of injecting a dependency.
     *
     * @param object $target The instance which needs the dependency
     * @param string $name Name of the property to be injected
     * @param object $dependency The dependency to inject – usually an object but can also be any other type
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function inject($target, $name, $dependency)
    {
        if (!is_object($target)) {
            throw new \InvalidArgumentException('Wrong type for argument $target, must be object.');
        }

        $objectReflection = new \ReflectionObject($target);
        $methodNamePart = strtoupper($name[0]) . substr($name, 1);
        if ($objectReflection->hasMethod('set' . $methodNamePart)) {
            $methodName = 'set' . $methodNamePart;
            $target->$methodName($dependency);
        } elseif ($objectReflection->hasMethod('inject' . $methodNamePart)) {
            $methodName = 'inject' . $methodNamePart;
            $target->$methodName($dependency);
        } elseif ($objectReflection->hasProperty($name)) {
            $property = $objectReflection->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($target, $dependency);
        } else {
            throw new \RuntimeException('Could not inject ' . $name . ' into object of type ' . get_class($target));
        }
    }
}
