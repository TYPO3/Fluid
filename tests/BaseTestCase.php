<?php
namespace TYPO3Fluid\Fluid\Tests;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The mother of all test cases.
 *
 * Don't sub class this test case but rather choose a more specialized base test case,
 * such as UnitTestCase or FunctionalTestCase
 *
 * @api
 */
abstract class BaseTestCase extends TestCase
{

    /**
     * Enable or disable the backup and restoration of static attributes.
     * @var boolean
     */
    protected $backupStaticAttributes = false;

    /**
     * Returns a mock object which allows for calling protected methods and access
     * of protected properties.
     *
     * @param string $originalClassName Full qualified name of the original class
     * @param array $methods
     * @param array $arguments
     * @param string $mockClassName
     * @param boolean $callOriginalConstructor
     * @param boolean $callOriginalClone
     * @param boolean $callAutoload
     * @return MockObject
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
        static::assertEquals($expected, static::extractNonPublicAttribute($actualClassOrObject, $actualAttributeName));
    }

    public static function assertAttributeSame($expected, string $actualAttributeName, $actualClassOrObject, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
    {
        static::assertSame($expected, static::extractNonPublicAttribute($actualClassOrObject, $actualAttributeName));
    }

    public static function assertAttributeContains($needle, string $haystackAttributeName, $haystackClassOrObject, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
    {
        static::assertContains($needle, static::extractNonPublicAttribute($haystackClassOrObject, $haystackAttributeName));
    }

    public static function assertAttributeNotEmpty(string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
    {
        static::assertNotEmpty(static::extractNonPublicAttribute($haystackClassOrObject, $haystackAttributeName));
    }

    public static function assertAttributeInstanceOf(string $expected, string $attributeName, $classOrObject, string $message = ''): void
    {
        static::assertInstanceOf($expected, static::extractNonPublicAttribute($classOrObject, $attributeName));
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
     * @param string $originalClassName Full qualified name of the original class
     * @param array $methods
     * @param array $arguments
     * @param boolean $callOriginalConstructor
     * @param boolean $callOriginalClone
     * @param boolean $callAutoload
     * @return MockObject
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
     * @param string $originalClassName Full qualified name of the original class
     * @param array $arguments
     * @param string $mockClassName
     * @param boolean $callOriginalConstructor
     * @param boolean $callOriginalClone
     * @param boolean $callAutoload
     * @return \PHPUnit_Framework_MockObject_MockObject
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
     * @param string $className Full qualified name of the original class
     * @return string Full qualified name of the built class
     * @api
     */
    protected function buildAccessibleProxy($className)
    {
        $accessibleClassName = 'AccessibleTestProxy' . md5(uniqid(mt_rand(), true));
        $class = new \ReflectionClass($className);
        $abstractModifier = $class->isAbstract() ? 'abstract ' : '';
        eval('
			' . $abstractModifier . 'class ' . $accessibleClassName . ' extends ' . $className . ' {
				public function _call($methodName) {
					return call_user_func_array(array($this, $methodName), array_slice(func_get_args(), 1));
				}
				public function _callRef($methodName, &$arg1 = NULL, &$arg2 = NULL, &$arg3 = NULL, &$arg4 = NULL, &$arg5= NULL, &$arg6 = NULL, &$arg7 = NULL, &$arg8 = NULL, &$arg9 = NULL) {
					switch (func_num_args()) {
						case 0 : return $this->$methodName();
						case 1 : return $this->$methodName($arg1);
						case 2 : return $this->$methodName($arg1, $arg2);
						case 3 : return $this->$methodName($arg1, $arg2, $arg3);
						case 4 : return $this->$methodName($arg1, $arg2, $arg3, $arg4);
						case 5 : return $this->$methodName($arg1, $arg2, $arg3, $arg4, $arg5);
						case 6 : return $this->$methodName($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
						case 7 : return $this->$methodName($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7);
						case 8 : return $this->$methodName($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8);
						case 9 : return $this->$methodName($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9);
					}
				}
				public function _set($propertyName, $value) {
					$this->$propertyName = $value;
				}
				public function _setRef($propertyName, &$value) {
					$this->$propertyName = $value;
				}
				public function _get($propertyName) {
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
     * @return void
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
