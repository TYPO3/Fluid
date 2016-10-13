<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Variables;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
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
}
