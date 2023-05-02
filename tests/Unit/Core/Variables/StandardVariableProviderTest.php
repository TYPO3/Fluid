<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Variables;

use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithoutToString;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class StandardVariableProviderTest extends UnitTestCase
{
    public static function getOperabilityTestValues(): array
    {
        return [
            [[], []],
            [['foo' => 'bar'], ['foo' => 'bar']]
        ];
    }

    /**
     * @dataProvider getOperabilityTestValues
     * @test
     */
    public function testOperability(array $input, array $expected): void
    {
        $provider = new StandardVariableProvider();
        $provider->setSource($input);
        self::assertEquals($input, $provider->getSource());
        self::assertEquals($expected, $provider->getAll());
        self::assertEquals(array_keys($expected), $provider->getAllIdentifiers());
        foreach ($expected as $key => $value) {
            self::assertEquals($value, $provider->get($key));
        }
    }

    /**
     * @test
     */
    public function testSupportsDottedPath(): void
    {
        $provider = new StandardVariableProvider();
        $provider->setSource(['foo' => ['bar' => 'baz']]);
        $result = $provider->getByPath('foo.bar');
        self::assertEquals('baz', $result);
    }

    /**
     * @test
     */
    public function testUnsetAsArrayAccess(): void
    {
        $variableProvider = $this->getMock(StandardVariableProvider::class, []);
        $variableProvider->add('variable', 'test');
        unset($variableProvider['variable']);
        self::assertFalse($variableProvider->exists('variable'));
    }

    /**
     * @test
     */
    public function addedObjectsCanBeRetrievedAgain(): void
    {
        $object = 'StringObject';
        $variableProvider = $this->getMock(StandardVariableProvider::class, []);
        $variableProvider->add('variable', $object);
        self::assertSame($variableProvider->get('variable'), $object, 'The retrieved object from the context is not the same as the stored object.');
    }

    /**
     * @test
     */
    public function addedObjectsCanBeRetrievedAgainUsingArrayAccess(): void
    {
        $object = 'StringObject';
        $variableProvider = $this->getMock(StandardVariableProvider::class, []);
        $variableProvider['variable'] = $object;
        self::assertSame($variableProvider->get('variable'), $object);
        self::assertSame($variableProvider['variable'], $object);
    }

    /**
     * @test
     */
    public function addedObjectsExistInArray(): void
    {
        $object = 'StringObject';
        $variableProvider = $this->getMock(StandardVariableProvider::class, []);
        $variableProvider->add('variable', $object);
        self::assertTrue($variableProvider->exists('variable'));
        self::assertTrue(isset($variableProvider['variable']));
    }

    /**
     * @test
     */
    public function addedObjectsExistInAllIdentifiers(): void
    {
        $object = 'StringObject';
        $variableProvider = $this->getMock(StandardVariableProvider::class, []);
        $variableProvider->add('variable', $object);
        self::assertEquals($variableProvider->getAllIdentifiers(), ['variable'], 'Added key is not visible in getAllIdentifiers');
    }

    /**
     * @test
     */
    public function gettingNonexistentValueReturnsNull(): void
    {
        $variableProvider = $this->getMock(StandardVariableProvider::class, []);
        $result = $variableProvider->get('nonexistent');
        self::assertNull($result);
    }

    /**
     * @test
     */
    public function removeReallyRemovesVariables(): void
    {
        $variableProvider = $this->getMock(StandardVariableProvider::class, []);
        $variableProvider->add('variable', 'string1');
        $variableProvider->remove('variable');
        $result = $variableProvider->get('variable');
        self::assertNull($result);
    }

    /**
     * @test
     */
    public function getAllShouldReturnAllVariables(): void
    {
        $variableProvider = $this->getMock(StandardVariableProvider::class, []);
        $variableProvider->add('name', 'Simon');
        self::assertSame(['name' => 'Simon'], $variableProvider->getAll());
    }

    /**
     * @test
     */
    public function testSleepReturnsExpectedPropertyNames(): void
    {
        $subject = new StandardVariableProvider();
        $properties = $subject->__sleep();
        self::assertContains('variables', $properties);
    }

    /**
     * @test
     */
    public function testGetScopeCopyReturnsCopyWithSettings(): void
    {
        $subject = new StandardVariableProvider(['foo' => 'bar', 'settings' => ['baz' => 'bam']]);
        $copy = $subject->getScopeCopy(['bar' => 'foo']);
        self::assertAttributeEquals(['settings' => ['baz' => 'bam'], 'bar' => 'foo'], 'variables', $copy);
    }

    public static function getPathTestValues(): array
    {
        $namedUser = new UserWithoutToString('Foobar Name');
        $unnamedUser = new UserWithoutToString('');
        return [
            [['foo' => 'bar'], 'foo', 'bar'],
            [['foo' => 'bar'], 'foo.invalid', null],
            [['user' => $namedUser], 'user.name', 'Foobar Name'],
            [['user' => $unnamedUser], 'user.name', ''],
            [['user' => $namedUser], 'user.named', true],
            [['user' => $unnamedUser], 'user.named', false],
            [['user' => $namedUser], 'user.invalid', null],
            [['foodynamicbar' => 'test', 'dyn' => 'dynamic'], 'foo{dyn}bar', 'test'],
            [['foo' => ['dynamic' => ['bar' => 'test']], 'dyn' => 'dynamic'], 'foo.{dyn}.bar', 'test'],
            [['foo' => ['bar' => 'test'], 'dynamic' => ['sub' => 'bar'], 'baz' => 'sub'], 'foo.{dynamic.{baz}}', 'test'],
            [['user' => $namedUser], 'user.hasAccessor', true],
            [['user' => $namedUser], 'user.isAccessor', true],
            [['user' => $unnamedUser], 'user.hasAccessor', false],
            [['user' => $unnamedUser], 'user.isAccessor', false],
        ];
    }

    /**
     * @param mixed $expected
     * @test
     * @dataProvider getPathTestValues
     */
    public function testGetByPath(array $subject, string $path, $expected): void
    {
        $provider = new StandardVariableProvider($subject);
        $result = $provider->getByPath($path);
        self::assertEquals($expected, $result);
    }

    public static function getExtractRedectAccessorTestValues(): array
    {
        return [
            [['test' => 'test'], 'test', null, 'test'],
            [['test' => 'test'], 'test', 'garbageextractionname', 'test'],
            [['test' => 'test'], 'test', StandardVariableProvider::ACCESSOR_PUBLICPROPERTY, 'test'],
            [['test' => 'test'], 'test', StandardVariableProvider::ACCESSOR_GETTER, 'test'],
            [['test' => 'test'], 'test', StandardVariableProvider::ACCESSOR_ASSERTER, 'test'],
        ];
    }

    /**
     * @param string $accessor
     * @test
     * @dataProvider getExtractRedectAccessorTestValues
     */
    public function testExtractRedetectsAccessorIfUnusableAccessorPassed(array $subject, string $path, $accessor, string $expected): void
    {
        $provider = new StandardVariableProvider($subject);
        $result = $provider->getByPath($path, [$accessor]);
        self::assertEquals($expected, $result);
    }
}
