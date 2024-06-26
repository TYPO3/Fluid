<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Variables;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Variables\ScopedVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;

final class ScopedVariableProviderTest extends TestCase
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

    #[DataProvider('getAllDataProvider')]
    #[Test]
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

    #[DataProvider('getAllIdentifiersDataProvider')]
    #[Test]
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

    #[DataProvider('getVariableDataProvider')]
    #[Test]
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

        yield 'local undefined subkey overrides global set subkey' => [
            ['myVar' => ['myKey' => 'global']],
            ['myVar' => ['myKey' => null]],
            'myVar.myKey',
            null,
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

    #[DataProvider('getVariableByPathDataProvider')]
    #[Test]
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

    #[DataProvider('variableExistsDataProvider')]
    #[Test]
    public function variableExists(array $globalVariables, array $localVariables, string $identifier, bool $exists): void
    {
        $variableProvider = new ScopedVariableProvider(
            new StandardVariableProvider($globalVariables),
            new StandardVariableProvider($localVariables),
        );
        self::assertEquals($exists, $variableProvider->exists($identifier));
    }

    #[Test]
    public function setVariableInConstructor()
    {
        $variableProvider = new ScopedVariableProvider(
            new StandardVariableProvider(['globalVar' => 'global']),
            new StandardVariableProvider(),
        );
        self::assertEquals('global', $variableProvider->getGlobalVariableProvider()->get('globalVar'));
        self::assertNull($variableProvider->getLocalVariableProvider()->get('globalVar'));
    }

    #[Test]
    public function addVariable()
    {
        $variableProvider = new ScopedVariableProvider(new StandardVariableProvider(), new StandardVariableProvider());
        $variableProvider->add('globalVar', 'global');
        self::assertEquals('global', $variableProvider->getGlobalVariableProvider()->get('globalVar'));
        self::assertEquals('global', $variableProvider->getLocalVariableProvider()->get('globalVar'));
    }

    #[Test]
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

    #[Test]
    public function getScopedCopy(): void
    {
        $variableProvider = new ScopedVariableProvider(
            new StandardVariableProvider(['myVar' => 'global', 'globalVariable' => 'global', 'settings' => ['test' => 'global']]),
            new StandardVariableProvider(['myVar' => 'local', 'localVariable' => 'local']),
        );

        $copy = $variableProvider->getScopeCopy(['myVar' => 'scoped']);
        self::assertNull($copy->get('globalVariable'));
        self::assertNull($copy->get('localVariable'));
        self::assertEquals('scoped', $copy->get('myVar'));
        self::assertEquals('global', $copy->get('settings.test'));

        $variableProvider->getGlobalVariableProvider()->add('addedGlobalVariable', 'added');
        $variableProvider->getLocalVariableProvider()->add('addedLocalVariable', 'added');
        self::assertNull($copy->get('addedGlobalVariable'));
        self::assertNull($copy->get('addedLocalVariable'));

        $copy->add('addedVariable', 'added');
        self::assertEquals('added', $copy->get('addedVariable'));
        self::assertNull($variableProvider->get('addedVariable'));
    }
}
