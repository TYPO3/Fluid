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
use TYPO3Fluid\Fluid\Core\Variables\JSONVariableProvider;

final class JSONVariableProviderTest extends TestCase
{
    public static function provideVariablesDataProvider(): array
    {
        return [
            ['{}', []],
            ['{"foo": "bar"}', ['foo' => 'bar']],
            [__DIR__ . '/Fixtures/test.json', ['foo' => 'bar']],
        ];
    }

    #[DataProvider('provideVariablesDataProvider')]
    #[Test]
    public function provideVariables(string $input, array $expected): void
    {
        $provider = new JSONVariableProvider();
        $provider->setSource($input);
        self::assertEquals($input, $provider->getSource());
        self::assertEquals($expected, $provider->getAll());
        self::assertEquals(array_keys($expected), $provider->getAllIdentifiers());
        foreach ($expected as $key => $value) {
            self::assertEquals($value, $provider->get($key));
        }
    }

    #[Test]
    public function getAllLoadJsonFile(): void
    {
        $provider = new JSONVariableProvider();
        $provider->setSource(__DIR__ . '/Fixtures/test.json');
        self::assertEquals(['foo' => 'bar'], $provider->getAll());
    }

    #[Test]
    public function getAllIdentifiersLoadJsonFile(): void
    {
        $provider = new JSONVariableProvider();
        $provider->setSource(__DIR__ . '/Fixtures/test.json');
        self::assertEquals(['foo'], $provider->getAllIdentifiers());
    }

    #[Test]
    public function getLoadJsonFile(): void
    {
        $provider = new JSONVariableProvider();
        $provider->setSource(__DIR__ . '/Fixtures/test.json');
        self::assertEquals('bar', $provider->get('foo'));
    }

    #[Test]
    public function getByPathLoadJsonFile(): void
    {
        $provider = new JSONVariableProvider();
        $provider->setSource(__DIR__ . '/Fixtures/test.json');
        self::assertEquals('bar', $provider->getByPath('foo'));
    }
}
