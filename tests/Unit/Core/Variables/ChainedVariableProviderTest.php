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

final class ChainedVariableProviderTest extends UnitTestCase
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
     * @dataProvider getGetTestValues
     * @test
     */
    public function getReturnsPreviouslySetSourceVariables(array $local, array $chain, string $path, string|null $expected): void
    {
        $subject = new ChainedVariableProvider($chain);
        $subject->setSource($local);
        self::assertEquals($expected, $subject->get($path));
    }

    /**
     * @dataProvider getGetTestValues
     * @test
     */
    public function getByPathReturnsPreviouslySetSourceVariables(array $local, array $chain, string $path, string|null $expected): void
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

    /**
     * @dataProvider getAllReturnsPreviouslySetSourceVariablesDataProvider
     * @test
     */
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

    /**
     * @dataProvider getAllIdentifiersReturnsPreviouslySetSourceIdentifiersDataProvider
     * @test
     */
    public function getAllIdentifiersReturnsPreviouslySetSourceIdentifiers(array $local, array $chain, array $expected): void
    {
        $subject = new ChainedVariableProvider($chain);
        $subject->setSource($local);
        self::assertEquals($expected, $subject->getAllIdentifiers());
    }

    /**
     * @test
     */
    public function getScopeCopyKeepsExistingVariableProviders(): void
    {
        $chain = [new StandardVariableProvider(['a' => 'a']), new StandardVariableProvider()];
        $subject = new ChainedVariableProvider($chain);
        $copy = $subject->getScopeCopy([]);
        self::assertSame('a', $copy->get('a'));
    }
}
