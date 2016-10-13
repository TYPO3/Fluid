<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Variables;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use TYPO3Fluid\Fluid\Core\Variables\JSONVariableProvider;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for JSONVariableProvider
 */
class JSONVariableProviderTest extends UnitTestCase
{

    /**
     * @var vfsStreamFile
     */
    protected $jsonFile;

    /**
     * Constructor
     */
    public function setUp()
    {
        $this->jsonFile = new vfsStreamFile('test.json');
        $this->jsonFile->setContent('{"foo": "bar"}');
        vfsStream::setup()->addChild($this->jsonFile);
    }

    /**
     * @dataProvider getOperabilityTestValues
     * @param string $input
     * @param array $expected
     */
    public function testOperability($input, array $expected)
    {
        $provider = new JSONVariableProvider();
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
            ['{}', []],
            ['{"foo": "bar"}', ['foo' => 'bar']],
            ['vfs://root/test.json', ['foo' => 'bar']],
        ];
    }
}
