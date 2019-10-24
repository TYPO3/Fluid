<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Variables;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithoutToString;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for TemplateVariableContainer
 */
class StandardVariableProviderTest extends UnitTestCase
{

    /**
     * @var StandardVariableProvider
     */
    protected $variableProvider;

    /**
     */
    public function setUp()
    {
        $this->variableProvider = $this->getMock(StandardVariableProvider::class, ['dummy']);
    }

    /**
     */
    public function tearDown()
    {
        unset($this->variableProvider);
    }

    /**
     * @dataProvider getOperabilityTestValues
     * @param string $input
     * @param array $expected
     */
    public function testOperability($input, array $expected)
    {
        $provider = new StandardVariableProvider();
        $provider->setSource($input);
        $this->assertEquals($input, $provider->getSource());
        $this->assertEquals($expected, $provider->getAll());
        $this->assertEquals(array_keys($expected), $provider->getAllIdentifiers());
        foreach ($expected as $key => $value) {
            $this->assertEquals($value, $provider->get($key));
        }
    }

    /**
     * @test
     */
    public function getOperabilityTestValues()
    {
        return [
            [[], []],
            [['foo' => 'bar'], ['foo' => 'bar']]
        ];
    }

    /**
     * @test
     */
    public function testSupportsDottedPath()
    {
        $provider = new StandardVariableProvider();
        $provider->setSource(['foo' => ['bar' => 'baz']]);
        $result = $provider->getByPath('foo.bar');
        $this->assertEquals('baz', $result);
    }

    /**
     * @test
     */
    public function testUnsetAsArrayAccess()
    {
        $this->variableProvider->add('variable', 'test');
        unset($this->variableProvider['variable']);
        $this->assertFalse($this->variableProvider->exists('variable'));
    }

    /**
     * @test
     */
    public function addedObjectsCanBeRetrievedAgain()
    {
        $object = 'StringObject';
        $this->variableProvider->add('variable', $object);
        $this->assertSame($this->variableProvider->get('variable'), $object, 'The retrieved object from the context is not the same as the stored object.');
    }

    /**
     * @test
     */
    public function addedObjectsCanBeRetrievedAgainUsingArrayAccess()
    {
        $object = 'StringObject';
        $this->variableProvider['variable'] = $object;
        $this->assertSame($this->variableProvider->get('variable'), $object);
        $this->assertSame($this->variableProvider['variable'], $object);
    }

    /**
     * @test
     */
    public function addedObjectsExistInArray()
    {
        $object = 'StringObject';
        $this->variableProvider->add('variable', $object);
        $this->assertTrue($this->variableProvider->exists('variable'));
        $this->assertTrue(isset($this->variableProvider['variable']));
    }

    /**
     * @test
     */
    public function addedObjectsExistInAllIdentifiers()
    {
        $object = 'StringObject';
        $this->variableProvider->add('variable', $object);
        $this->assertEquals($this->variableProvider->getAllIdentifiers(), ['variable'], 'Added key is not visible in getAllIdentifiers');
    }

    /**
     * @test
     */
    public function gettingNonexistentValueReturnsNull()
    {
        $result = $this->variableProvider->get('nonexistent');
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function removeReallyRemovesVariables()
    {
        $this->variableProvider->add('variable', 'string1');
        $this->variableProvider->remove('variable');
        $result = $this->variableProvider->get('variable');
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function getAllShouldReturnAllVariables()
    {
        $this->variableProvider->add('name', 'Simon');
        $this->assertSame(['name' => 'Simon'], $this->variableProvider->getAll());
    }

    /**
     * @test
     */
    public function testSleepReturnsExpectedPropertyNames()
    {
        $subject = new StandardVariableProvider();
        $properties = $subject->__sleep();
        $this->assertContains('variables', $properties);
    }

    /**
     * @test
     */
    public function testGetScopeCopyReturnsCopyWithSettings()
    {
        $subject = new StandardVariableProvider(['foo' => 'bar', 'settings' => ['baz' => 'bam']]);
        $copy = $subject->getScopeCopy(['bar' => 'foo']);
        $this->assertAttributeEquals(['settings' => ['baz' => 'bam'], 'bar' => 'foo'], 'variables', $copy);
    }

    /**
     * @param mixed $subject
     * @param string $path
     * @param mixed $expected
     * @test
     * @dataProvider getPathTestValues
     */
    public function testGetByPath($subject, $path, $expected)
    {
        $provider = new StandardVariableProvider($subject);
        $result = $provider->getByPath($path);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getPathTestValues()
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
            [['user' => $namedUser], 'user.hasAccessor', true],
            [['user' => $namedUser], 'user.isAccessor', true],
            [['user' => $unnamedUser], 'user.hasAccessor', false],
            [['user' => $unnamedUser], 'user.isAccessor', false],
        ];
    }

    /**
     * @param mixed $subject
     * @param string $path
     * @param mixed $expected
     * @test
     * @dataProvider getAccessorsForPathTestValues
     */
    public function testGetAccessorsForPath($subject, $path, $expected)
    {
        $provider = new StandardVariableProvider($subject);
        $result = $provider->getAccessorsForPath($path);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getAccessorsForPathTestValues()
    {
        $namedUser = new UserWithoutToString('Foobar Name');
        $inArray = ['user' => $namedUser];
        $inArrayAccess = new StandardVariableProvider($inArray);
        $inPublic = (object) $inArray;
        $asArray = StandardVariableProvider::ACCESSOR_ARRAY;
        $asGetter = StandardVariableProvider::ACCESSOR_GETTER;
        $asPublic = StandardVariableProvider::ACCESSOR_PUBLICPROPERTY;
        return [
            [['inArray' => $inArray], 'inArray.user', [$asArray, $asArray]],
            [['inArray' => $inArray], 'inArray.user.name', [$asArray, $asArray, $asGetter]],
            [['inArrayAccess' => $inArrayAccess], 'inArrayAccess.user.name', [$asArray, $asArray, $asGetter]],
            [['inArrayAccessWithGetter' => $inArrayAccess], 'inArrayAccessWithGetter.allIdentifiers', [$asArray, $asGetter]],
            [['inPublic' => $inPublic], 'inPublic.user.name', [$asArray, $asPublic, $asGetter]],
        ];
    }

    /**
     * @param mixed $subject
     * @param string $path
     * @param string $accessor
     * @param mixed $expected
     * @test
     * @dataProvider getExtractRedectAccessorTestValues
     */
    public function testExtractRedetectsAccessorIfUnusableAccessorPassed($subject, $path, $accessor, $expected)
    {
        $provider = new StandardVariableProvider($subject);
        $result = $provider->getByPath($path, [$accessor]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getExtractRedectAccessorTestValues()
    {
        return [
            [['test' => 'test'], 'test', null, 'test'],
            [['test' => 'test'], 'test', 'garbageextractionname', 'test'],
            [['test' => 'test'], 'test', StandardVariableProvider::ACCESSOR_PUBLICPROPERTY, 'test'],
            [['test' => 'test'], 'test', StandardVariableProvider::ACCESSOR_GETTER, 'test'],
            [['test' => 'test'], 'test', StandardVariableProvider::ACCESSOR_ASSERTER, 'test'],
        ];
    }
}
