<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Variables;

use TYPO3Fluid\Fluid\Core\Variables\ScopedVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

final class ScopedVariableProviderTest extends UnitTestCase
{
    public static function getAllDataProvider(): \Generator
    {
        yield 'no variables' => [
            [],
            [],
            [],
        ];

        yield 'only global variables' => [
            ['myVar' => 'global'],
            [],
            ['myVar' => 'global'],
        ];

        yield 'only local variables' => [
            [],
            ['myVar' => 'local'],
            ['myVar' => 'local'],
        ];

        yield 'local overwrites global' => [
            ['myGlobalVar' => 'global', 'myVar' => 'global'],
            ['myLocalVar' => 'local', 'myVar' => 'local'],
            ['myGlobalVar' => 'global', 'myVar' => 'local', 'myLocalVar' => 'local'],
        ];
    }

    /**
     * @test
     * @dataProvider getAllDataProvider
     */
    public function getAllVariables(array $globalVariables, array $localVariables, array $result): void
    {
        $variableProvider = new ScopedVariableProvider(
            new StandardVariableProvider($globalVariables),
            new StandardVariableProvider($localVariables),
        );
        self::assertEquals($result, $variableProvider->getAll());
    }

    public static function getAllIdentifiersDataProvider(): \Generator
    {
        yield 'no variables' => [
            [],
            [],
            [],
        ];

        yield 'only global variables' => [
            ['myVar' => 'global'],
            [],
            ['myVar'],
        ];

        yield 'only local variables' => [
            [],
            ['myVar' => 'local'],
            ['myVar'],
        ];

        yield 'local overwrites global' => [
            ['myGlobalVar' => 'global', 'myVar' => 'global'],
            ['myLocalVar' => 'local', 'myVar' => 'local'],
            ['myGlobalVar', 'myVar', 'myLocalVar'],
        ];
    }

    /**
     * @test
     * @dataProvider getAllIdentifiersDataProvider
     */
    public function getAllIdentifiers(array $globalVariables, array $localVariables, array $result): void
    {
        $variableProvider = new ScopedVariableProvider(
            new StandardVariableProvider($globalVariables),
            new StandardVariableProvider($localVariables),
        );
        self::assertEquals($result, $variableProvider->getAllIdentifiers());
    }

    public static function getVariableDataProvider(): \Generator
    {
        yield 'no variables' => [
            [],
            [],
            'myVar',
            null,
        ];

        yield 'only global variables' => [
            ['myVar' => 'global'],
            [],
            'myVar',
            'global',
        ];

        yield 'only local variables' => [
            [],
            ['myVar' => 'local'],
            'myVar',
            'local',
        ];

        yield 'local overwrites global' => [
            ['myGlobalVar' => 'global', 'myVar' => 'global'],
            ['myLocalVar' => 'local', 'myVar' => 'local'],
            'myVar',
            'local',
        ];
    }

    /**
     * @test
     * @dataProvider getVariableDataProvider
     */
    public function getVariable(array $globalVariables, array $localVariables, string $identifier, $result): void
    {
        $variableProvider = new ScopedVariableProvider(
            new StandardVariableProvider($globalVariables),
            new StandardVariableProvider($localVariables),
        );
        self::assertEquals($result, $variableProvider->get($identifier));
    }

    public static function getVariableByPathDataProvider(): \Generator
    {
        yield 'no variables' => [
            [],
            [],
            'myVar',
            null,
        ];

        yield 'only global variables' => [
            ['myVar' => 'global'],
            [],
            'myVar',
            'global',
        ];

        yield 'only local variables' => [
            [],
            ['myVar' => 'local'],
            'myVar',
            'local',
        ];

        yield 'local overwrites global' => [
            ['myGlobalVar' => 'global', 'myVar' => 'global'],
            ['myLocalVar' => 'local', 'myVar' => 'local'],
            'myVar',
            'local',
        ];

        yield 'nested arrays' => [
            ['myGlobalVar' => 'global', 'myVar' => ['myKey' => 'global', 'anotherKey' => 'anotherValue']],
            ['myLocalVar' => 'local', 'myVar' => ['myKey' => 'local', 'anotherKey' => 'yetAnotherValue']],
            'myVar.myKey',
            'local',
        ];

        yield 'variable variables using only globals' => [
            ['myVar' => ['sub' => 'global'], 'path' => 'sub'],
            [],
            'myVar.{path}',
            'global',
        ];

        yield 'variable variables using only locals' => [
            [],
            ['myVar' => ['sub' => 'local'], 'path' => 'sub'],
            'myVar.{path}',
            'local',
        ];

        yield 'variable variables using local in global' => [
            ['myVar' => ['sub' => 'global']],
            ['path' => 'sub'],
            'myVar.{path}',
            'global',
        ];

        yield 'variable variables using global in local' => [
            ['path' => 'sub'],
            ['myVar' => ['sub' => 'local']],
            'myVar.{path}',
            'local',
        ];
    }

    /**
     * @test
     * @dataProvider getVariableByPathDataProvider
     */
    public function getVariableByPath(array $globalVariables, array $localVariables, string $path, $result): void
    {
        $variableProvider = new ScopedVariableProvider(
            new StandardVariableProvider($globalVariables),
            new StandardVariableProvider($localVariables),
        );
        self::assertEquals($result, $variableProvider->getByPath($path));
    }

    public static function variableExistsDataProvider(): \Generator
    {
        yield 'no variables' => [
            [],
            [],
            'myVar',
            false,
        ];

        yield 'only global variables' => [
            ['myVar' => 'global'],
            [],
            'myVar',
            true,
        ];

        yield 'only local variables' => [
            [],
            ['myVar' => 'local'],
            'myVar',
            true,
        ];

        yield 'local overwrites global' => [
            ['myGlobalVar' => 'global', 'myVar' => 'global'],
            ['myLocalVar' => 'local', 'myVar' => 'local'],
            'myVar',
            true,
        ];
    }

    /**
     * @test
     * @dataProvider variableExistsDataProvider
     */
    public function variableExists(array $globalVariables, array $localVariables, string $identifier, bool $exists): void
    {
        $variableProvider = new ScopedVariableProvider(
            new StandardVariableProvider($globalVariables),
            new StandardVariableProvider($localVariables),
        );
        self::assertEquals($exists, $variableProvider->exists($identifier));
    }

    /**
     * @test
     */
    public function setVariableInConstructor()
    {
        $variableProvider = new ScopedVariableProvider(
            new StandardVariableProvider(['globalVar' => 'global']),
            new StandardVariableProvider(),
        );
        self::assertEquals('global', $variableProvider->getGlobalVariableProvider()->get('globalVar'));
        self::assertNull($variableProvider->getLocalVariableProvider()->get('globalVar'));
    }

    /**
     * @test
     */
    public function addVariable()
    {
        $variableProvider = new ScopedVariableProvider(new StandardVariableProvider(), new StandardVariableProvider());
        $variableProvider->add('globalVar', 'global');
        self::assertEquals('global', $variableProvider->getGlobalVariableProvider()->get('globalVar'));
        self::assertNull($variableProvider->getLocalVariableProvider()->get('globalVar'));
    }

    /**
     * @test
     */
    public function removeVariable(): void
    {
        $variableProvider = new ScopedVariableProvider(
            new StandardVariableProvider(['myVar' => 'global']),
            new StandardVariableProvider(['myVar' => 'local']),
        );
        $variableProvider->remove('myVar');
        self::assertNull($variableProvider->getGlobalVariableProvider()->get('globalVar'));
        self::assertNull($variableProvider->getLocalVariableProvider()->get('globalVar'));
    }
}
