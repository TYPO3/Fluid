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
use TYPO3Fluid\Fluid\Core\Variables\ChainedVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;

final class ChainedVariableProviderTest extends TestCase
{
    public static function getGetTestValues(): array
    {
        $a = new StandardVariableProvider(['a' => 'a', 'c' => ['c'], 'd' => ['dk' => 'dv']]);
        $b = new StandardVariableProvider(['a' => 'b', 'b' => 'b']);
        return [
            [['a' => 'local'], [$a, $b], 'a', 'local'],
            [[], [$a, $b], 'a', 'a'],
            [[], [$a, $b], 'b', 'b'],
            [[], [$b, $a], 'a', 'b'],
            [[], [$b, $a], 'b', 'b'],
            [[], [$a, $b], 'c', [0 => 'c']],
            [[], [$a, $b], 'c.0', 'c'],
            [[], [$a, $b], 'd', ['dk' => 'dv']],
            [[], [$a, $b], 'd.dk', 'dv'],
            [['e' => 'e'], [$a, $b], 'a', 'a'],
            [['e' => 'e'], [$a, $b], 'e', 'e'],
            [['f' => ['f']], [$a, $b], 'f', ['f']],
            [['g' => ['g']], [$a, $b], 'g.0', 'g'],
            [['h' => ['hk' => 'hv']], [$a, $b], 'h.hk', 'hv'],
            [[], [$b, $a], 'notfound', null],
        ];
    }

    #[DataProvider('getGetTestValues')]
    #[Test]
    public function getReturnsPreviouslySetSourceVariables(array $local, array $chain, string $path, string|array|null $expected): void
    {
        $subject = new ChainedVariableProvider($chain);
        $subject->setSource($local);
        self::assertEquals($expected, $subject->get($path));
    }

    #[DataProvider('getGetTestValues')]
    #[Test]
    public function getByPathReturnsPreviouslySetSourceVariables(array $local, array $chain, string $path, string|array|null $expected): void
    {
        $subject = new ChainedVariableProvider($chain);
        $subject->setSource($local);
        self::assertEquals($expected, $subject->getByPath($path));
    }

    public static function getAllReturnsPreviouslySetSourceVariablesDataProvider(): array
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

    #[DataProvider('getAllReturnsPreviouslySetSourceVariablesDataProvider')]
    #[Test]
    public function getAllReturnsPreviouslySetSourceVariables(array $local, array $chain, array $expected): void
    {
        $subject = new ChainedVariableProvider($chain);
        $subject->setSource($local);
        self::assertEquals($expected, $subject->getAll());
    }

    public static function getAllIdentifiersReturnsPreviouslySetSourceIdentifiersDataProvider(): array
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

    #[DataProvider('getAllIdentifiersReturnsPreviouslySetSourceIdentifiersDataProvider')]
    #[Test]
    public function getAllIdentifiersReturnsPreviouslySetSourceIdentifiers(array $local, array $chain, array $expected): void
    {
        $subject = new ChainedVariableProvider($chain);
        $subject->setSource($local);
        self::assertEquals($expected, $subject->getAllIdentifiers());
    }

    #[Test]
    public function getScopeCopyKeepsExistingVariableProviders(): void
    {
        $chain = [new StandardVariableProvider(['a' => 'a']), new StandardVariableProvider()];
        $subject = new ChainedVariableProvider($chain);
        $copy = $subject->getScopeCopy([]);
        self::assertSame('a', $copy->get('a'));
    }
}
