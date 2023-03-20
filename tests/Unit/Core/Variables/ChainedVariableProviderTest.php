<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Variables;

use TYPO3Fluid\Fluid\Core\Variables\ChainedVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class ChainedVariableProviderTest extends UnitTestCase
{
    public static function getGetTestValues(): array
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
     * @param mixed $expected
     * @dataProvider getGetTestValues
     * @test
     */
    public function testGet(array $local, array $chain, string $path, $expected): void
    {
        $chainedProvider = new ChainedVariableProvider($chain);
        $chainedProvider->setSource($local);
        self::assertEquals($expected, $chainedProvider->get($path));
    }

    /**
     * @param mixed $expected
     * @dataProvider getGetTestValues
     */
    public function testGetByPath(array $local, array $chain, string $path, $expected): void
    {
        $chainedProvider = new ChainedVariableProvider($chain);
        $chainedProvider->setSource($local);
        self::assertEquals($expected, $chainedProvider->getByPath($path));
    }

    public static function getGetAllTestValues(): array
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
     * @dataProvider getGetAllTestValues
     * @test
     */
    public function testGetAll(array $local, array $chain, array $expected): void
    {
        $chainedProvider = new ChainedVariableProvider($chain);
        $chainedProvider->setSource($local);
        self::assertEquals($expected, $chainedProvider->getAll());
    }

    public static function getGetAllIdentifiersTestValues(): array
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
     * @dataProvider getGetAllIdentifiersTestValues
     * @test
     */
    public function testGetAllIdentifiers(array $local, array $chain, array $expected): void
    {
        $chainedProvider = new ChainedVariableProvider($chain);
        $chainedProvider->setSource($local);
        self::assertEquals($expected, $chainedProvider->getAllIdentifiers());
    }

    /**
     * @test
     */
    public function testGetScopeCopy(): void
    {
        $chain = [new StandardVariableProvider(), new StandardVariableProvider()];
        $chainedProvider = new ChainedVariableProvider($chain);
        $copy = $chainedProvider->getScopeCopy([]);
        self::assertAttributeSame($chain, 'variableProviders', $copy);
    }
}
