<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\RenderViewHelper;

class ViewHelperResolverTest extends UnitTestCase
{
    /**
     * @test
     */
    public function addNamespaceWithStringRecordsNamespace(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('t', 'test');
        self::assertSame(['test'], $subject->getNamespaces()['t']);
    }

    /**
     * @test
     */
    public function addNamespaceWithArrayRecordsNamespace(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('t', ['test']);
        self::assertSame(['test'], $subject->getNamespaces()['t']);
    }

    /**
     * @test
     */
    public function addNamespaceWithNullDoesNotChoke(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('ignored', null);
        self::assertSame(['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'ignored' => null], $subject->getNamespaces());
    }

    /**
     * @test
     */
    public function addNamespaceWithNullTwiceDoesNotChoke(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('ignored', null);
        $subject->addNamespace('ignored', null);
        self::assertSame(['f' => ['TYPO3Fluid\\Fluid\\ViewHelpers'], 'ignored' => null], $subject->getNamespaces());
    }

    /**
     * @test
     */
    public function addNamespaceWithNullAndThenValidValueConvertsToNotIgnoredNamespace(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('ignored', null);
        $subject->addNamespace('ignored', ['Foo\\Bar']);
        self::assertSame(['f' => ['TYPO3Fluid\\Fluid\\ViewHelpers'], 'ignored' => ['Foo\\Bar']], $subject->getNamespaces());
    }

    /**
     * @test
     */
    public function addNamespaceDoesNotThrowAnExceptionIfTheAliasExistAlreadyAndPointsToTheSamePhpNamespace(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('foo', 'Some\Namespace');
        self::assertSame(['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'foo' => ['Some\Namespace']], $subject->getNamespaces());
        $subject->addNamespace('foo', 'Some\Namespace');
        self::assertSame(['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'foo' => ['Some\Namespace']], $subject->getNamespaces());
    }

    /**
     * @test
     */
    public function setNamespacesSetsNamespaces(): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces(['t' => ['test']]);
        self::assertSame(['t' => ['test']], $subject->getNamespaces());
    }

    /**
     * @test
     */
    public function setNamespacesConvertsStringNamespaceToArray(): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces(['t' => 'test']);
        self::assertSame(['t' => ['test']], $subject->getNamespaces());
    }

    public static function isNamespaceValidReturnsExpectedValueDataProvider(): array
    {
        return [
            [['foo' => null], 'foo', false],
            [['foo' => ['test']], 'foo', true],
            [['foo' => ['test']], 'foobar', false],
            [['foo*' => null], 'foo', false],
            [[], 'invalid', false]
        ];
    }

    /**
     * @test
     * @dataProvider isNamespaceValidReturnsExpectedValueDataProvider
     */
    public function isNamespaceValidReturnsExpectedValue(array $namespaces, string $namespace, bool $expected): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces($namespaces);
        self::assertSame($expected, $subject->isNamespaceValid($namespace));
    }

    public static function isNamespaceIgnoredReturnsExpectedValueDataProvider(): array
    {
        return [
            [['foo' => null], 'foo', true],
            [['foo' => ['test']], 'foo', false],
            [['foo' => ['test']], 'foobar', false],
            [['foo*' => null], 'foobar', true],
        ];
    }

    /**
     * @test
     * @dataProvider isNamespaceIgnoredReturnsExpectedValueDataProvider
     */
    public function isNamespaceIgnoredReturnsExpectedValue(array $namespaces, string $namespace, bool $expected): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces($namespaces);
        self::assertSame($expected, $subject->isNamespaceIgnored($namespace));
    }

    public static function isNamespaceValidOrIgnoredReturnsExpectedValueDataProvider(): array
    {
        return [
            [['foo' => null], 'foo', true],
            [['foo' => ['test']], 'foo', true],
            [['foo' => ['test']], 'foobar', false],
            [['foo*' => null], 'foobar', true],
        ];
    }

    /**
     * @test
     * @dataProvider isNamespaceValidOrIgnoredReturnsExpectedValueDataProvider
     */
    public function isNamespaceValidOrIgnoredReturnsExpectedValue(array $namespaces, string $namespace, bool $expected): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces($namespaces);
        self::assertSame($expected, $subject->isNamespaceValidOrIgnored($namespace));
    }

    /**
     * @test
     */
    public function resolveViewHelperClassNameThrowsExceptionIfClassNotResolved(): void
    {
        $this->expectException(Exception::class);
        $subject = new ViewHelperResolver();
        $subject->resolveViewHelperClassName('f', 'invalid');
    }

    /**
     * @test
     */
    public function resolveViewHelperClassNameSupportsMultipleNamespaces(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('f', 'Foo1\\Bar1');
        $subject->addNamespace('f', 'TYPO3Fluid\\Fluid\\ViewHelpers');
        $subject->addNamespace('f', 'Foo2\\Bar2');
        self::assertSame('TYPO3Fluid\\Fluid\\ViewHelpers\\RenderViewHelper', $subject->resolveViewHelperClassName('f', 'render'));
    }

    /**
     * @test
     */
    public function resolveViewHelperClassNameDoesNotChokeOnNullInMultipleNamespaces(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('f', null);
        $subject->addNamespace('f', 'TYPO3Fluid\\Fluid\\ViewHelpers');
        $subject->addNamespace('f', null);
        self::assertSame('TYPO3Fluid\\Fluid\\ViewHelpers\\RenderViewHelper', $subject->resolveViewHelperClassName('f', 'render'));
    }

    /**
     * @test
     */
    public function resolveViewHelperClassNameTrimsBackslashSuffixFromNamespace(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('f', 'TYPO3Fluid\\Fluid\\ViewHelpers\\');
        self::assertSame('TYPO3Fluid\\Fluid\\ViewHelpers\\RenderViewHelper', $subject->resolveViewHelperClassName('f', 'render'));
    }

    public static function resolvePhpNamespaceFromFluidNamespaceDataProvider(): array
    {
        return [
            ['Foo\\Bar', 'Foo\\Bar\\ViewHelpers'],
            ['Foo\\Bar\\ViewHelpers', 'Foo\\Bar\\ViewHelpers'],
            ['http://typo3.org/ns/Foo/Bar/ViewHelpers', 'Foo\\Bar\\ViewHelpers'],
        ];
    }

    /**
     * @test
     * @dataProvider resolvePhpNamespaceFromFluidNamespaceDataProvider
     */
    public function resolvePhpNamespaceFromFluidNamespace(string $input, string $expected): void
    {
        $subject = new ViewHelperResolver();
        self::assertSame($expected, $subject->resolvePhpNamespaceFromFluidNamespace($input));
    }

    /**
     * @test
     */
    public function createViewHelperInstanceCreatesInstance(): void
    {
        $subject = new ViewHelperResolver();
        $result = $subject->createViewHelperInstance('f', 'render');
        self::assertInstanceOf(RenderViewHelper::class, $result);
    }
}
