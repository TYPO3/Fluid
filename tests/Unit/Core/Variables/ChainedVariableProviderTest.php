<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Variables;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Variables\ChainedVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for ChainedVariableContainer
 */
class ChainedVariableProviderTest extends UnitTestCase
{

    /**
     * @param array $local
     * @param VariableProviderInterface $chain
     * @param string $path
     * @param mixed $expected
     * @dataProvider getGetTestValues
     */
    public function testGet(array $local, array $chain, $path, $expected)
    {
        $chainedProvider = new ChainedVariableProvider($chain);
        $chainedProvider->setSource($local);
        $this->assertEquals($expected, $chainedProvider->get($path));
    }

    /**
     * @param array $local
     * @param VariableProviderInterface $chain
     * @param string $path
     * @param mixed $expected
     * @dataProvider getGetTestValues
     */
    public function testGetByPath(array $local, array $chain, $path, $expected)
    {
        $chainedProvider = new ChainedVariableProvider($chain);
        $chainedProvider->setSource($local);
        $this->assertEquals($expected, $chainedProvider->getByPath($path));
    }

    /**
     * @return array
     */
    public function getGetTestValues()
    {
        $a = new StandardVariableProvider(['a' => 'a']);
        $b = new StandardVariableProvider(['a' => 'b', 'b' => 'b']);

        return [
            [['a' => 'local'], [$a, $b], 'a', 'local'],
            [[], [$a, $b], 'a', 'a'],
            [[], [$a, $b], 'b', 'b'],
            [[], [$b, $a], 'a', 'b'],
            [[], [$b, $a], 'b', 'b'],
            [[], [$b, $a], 'notfound', null],
        ];
    }

    /**
     * @param array $local
     * @param VariableProviderInterface $chain
     * @param mixed $expected
     * @dataProvider getGetAllTestValues
     */
    public function testGetAll(array $local, array $chain, $expected)
    {
        $chainedProvider = new ChainedVariableProvider($chain);
        $chainedProvider->setSource($local);
        $this->assertEquals($expected, $chainedProvider->getAll());
    }

    /**
     * @return array
     */
    public function getGetAllTestValues()
    {
        $a = new StandardVariableProvider(['a' => 'a']);
        $b = new StandardVariableProvider(['a' => 'b', 'b' => 'b']);

        return [
            [['a' => 'local'], [$a, $b], ['a' => 'local', 'b' => 'b']],
            [[], [$a, $b], ['a' => 'a', 'b' => 'b']],
            [[], [$a, $b], ['a' => 'a', 'b' => 'b']],
            [[], [$b, $a], ['a' => 'b', 'b' => 'b']],
        ];
    }

    /**
     * @param array $local
     * @param VariableProviderInterface $chain
     * @param mixed $expected
     * @dataProvider getGetAllIdentifiersTestValues
     */
    public function testGetAllIdentifiers(array $local, array $chain, $expected)
    {
        $chainedProvider = new ChainedVariableProvider($chain);
        $chainedProvider->setSource($local);
        $this->assertEquals($expected, $chainedProvider->getAllIdentifiers());
    }

    /**
     * @return array
     */
    public function getGetAllIdentifiersTestValues()
    {
        $a = new StandardVariableProvider(['a' => 'a']);
        $b = new StandardVariableProvider(['a' => 'b', 'b' => 'b']);

        return [
            [['a' => 'local'], [$a, $b], ['a', 'b']],
            [[], [$a, $b], ['a', 'b']],
            [[], [$a, $b], ['a', 'b']],
            [[], [$b, $a], ['a', 'b']],
        ];
    }

    /**
     * @test
     */
    public function testGetScopeCopy()
    {
        $chain = [new StandardVariableProvider(), new StandardVariableProvider()];
        $chainedProvider = new ChainedVariableProvider($chain);
        $copy = $chainedProvider->getScopeCopy([]);
        $this->assertAttributeSame($chain, 'variableProviders', $copy);
    }
}
