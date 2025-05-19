<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperCollection;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\TestViewHelperResolverDelegate;
use TYPO3Fluid\Fluid\ViewHelpers\RenderViewHelper;

final class ViewHelperResolverTest extends TestCase
{
    #[Test]
    public function addNamespaceWithStringRecordsNamespace(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('t', 'test');
        self::assertSame(['test'], $subject->getNamespaces()['t']);
    }

    #[Test]
    public function addNamespaceWithArrayRecordsNamespace(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('t', ['test']);
        self::assertSame(['test'], $subject->getNamespaces()['t']);
    }

    #[Test]
    public function addNamespaceWithDelegateInstanceRecordsNamespace(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('t', new TestViewHelperResolverDelegate());
        $subject->addNamespace('t', new ViewHelperCollection('test'));
        self::assertSame([TestViewHelperResolverDelegate::class, 'test'], $subject->getNamespaces()['t']);
    }

    #[Test]
    public function addNamespaceWithNullDoesNotChoke(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('ignored', null);
        self::assertSame(['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'ignored' => null], $subject->getNamespaces());
    }

    #[Test]
    public function addNamespaceWithNullTwiceDoesNotChoke(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('ignored', null);
        $subject->addNamespace('ignored', null);
        self::assertSame(['f' => ['TYPO3Fluid\\Fluid\\ViewHelpers'], 'ignored' => null], $subject->getNamespaces());
    }

    #[Test]
    public function addNamespaceWithNullAndThenValidValueConvertsToNotIgnoredNamespace(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('ignored', null);
        $subject->addNamespace('ignored', ['Foo\\Bar']);
        self::assertSame(['f' => ['TYPO3Fluid\\Fluid\\ViewHelpers'], 'ignored' => ['Foo\\Bar']], $subject->getNamespaces());
    }

    #[Test]
    public function addNamespaceDoesNotThrowAnExceptionIfTheAliasExistAlreadyAndPointsToTheSamePhpNamespace(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('foo', 'Some\Namespace');
        self::assertSame(['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'foo' => ['Some\Namespace']], $subject->getNamespaces());
        $subject->addNamespace('foo', 'Some\Namespace');
        self::assertSame(['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'foo' => ['Some\Namespace']], $subject->getNamespaces());
    }

    #[Test]
    public function setNamespacesSetsNamespaces(): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces(['t' => ['test']]);
        self::assertSame(['t' => ['test']], $subject->getNamespaces());
    }

    #[Test]
    public function setNamespacesConvertsStringNamespaceToArray(): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces(['t' => 'test']);
        self::assertSame(['t' => ['test']], $subject->getNamespaces());
    }

    #[Test]
    public function setNamespacesConvertsDelegatesToStrings(): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces(['t' => new TestViewHelperResolverDelegate(), 'u' => [new ViewHelperCollection('test')]]);
        self::assertSame(['t' => [TestViewHelperResolverDelegate::class], 'u' => ['test']], $subject->getNamespaces());
    }

    public static function isNamespaceValidReturnsExpectedValueDataProvider(): array
    {
        return [
            [['foo' => null], 'foo', false],
            [['foo' => ['test']], 'foo', true],
            [['foo' => ['test']], 'foobar', false],
            [['foo*' => null], 'foo', false],
            [[], 'invalid', false],
        ];
    }

    #[DataProvider('isNamespaceValidReturnsExpectedValueDataProvider')]
    #[Test]
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

    #[DataProvider('isNamespaceIgnoredReturnsExpectedValueDataProvider')]
    #[Test]
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

    #[DataProvider('isNamespaceValidOrIgnoredReturnsExpectedValueDataProvider')]
    #[Test]
    #[IgnoreDeprecations]
    public function isNamespaceValidOrIgnoredReturnsExpectedValue(array $namespaces, string $namespace, bool $expected): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces($namespaces);
        self::assertSame($expected, $subject->isNamespaceValidOrIgnored($namespace));
    }

    #[Test]
    public function resolveViewHelperClassNameThrowsExceptionIfClassNotResolved(): void
    {
        $this->expectException(Exception::class);
        $subject = new ViewHelperResolver();
        $subject->resolveViewHelperClassName('f', 'invalid');
    }

    #[Test]
    public function resolveViewHelperClassNameSupportsMultipleNamespaces(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('f', 'Foo1\\Bar1');
        $subject->addNamespace('f', 'TYPO3Fluid\\Fluid\\ViewHelpers');
        $subject->addNamespace('f', 'Foo2\\Bar2');
        self::assertSame('TYPO3Fluid\\Fluid\\ViewHelpers\\RenderViewHelper', $subject->resolveViewHelperClassName('f', 'render'));
    }

    #[Test]
    public function resolveViewHelperClassNameSupportsResolverDelegates(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('f', 'Foo1\\Bar1');
        $subject->addNamespace('f', 'TYPO3Fluid\\Fluid\\ViewHelpers');
        $subject->addNamespace('f', TestViewHelperResolverDelegate::class);
        self::assertSame('TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\TestViewHelperResolverDelegate\Render', $subject->resolveViewHelperClassName('f', 'render'));
        self::assertSame('TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\TestViewHelperResolverDelegate\Render_Sub', $subject->resolveViewHelperClassName('f', 'render.sub'));
    }

    #[Test]
    public function resolveViewHelperClassNameDoesNotChokeOnNullInMultipleNamespaces(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('f', null);
        $subject->addNamespace('f', 'TYPO3Fluid\\Fluid\\ViewHelpers');
        $subject->addNamespace('f', null);
        self::assertSame('TYPO3Fluid\\Fluid\\ViewHelpers\\RenderViewHelper', $subject->resolveViewHelperClassName('f', 'render'));
    }

    #[Test]
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

    #[DataProvider('resolvePhpNamespaceFromFluidNamespaceDataProvider')]
    #[Test]
    #[IgnoreDeprecations]
    public function resolvePhpNamespaceFromFluidNamespace(string $input, string $expected): void
    {
        $subject = new ViewHelperResolver();
        self::assertSame($expected, $subject->resolvePhpNamespaceFromFluidNamespace($input));
    }

    #[Test]
    public function createViewHelperInstanceCreatesInstance(): void
    {
        $subject = new ViewHelperResolver();
        $result = $subject->createViewHelperInstance('f', 'render');
        self::assertInstanceOf(RenderViewHelper::class, $result);
    }

    public static function createResolverDelegateInstanceFromClassNameDataProvider(): iterable
    {
        return [
            [TestViewHelperResolverDelegate::class, TestViewHelperResolverDelegate::class],
            ['Vendor\\Package\\NonExistentClass', ViewHelperCollection::class],
            ['TYPO3Fluid\\Fluid\\ViewHelpers', ViewHelperCollection::class],
        ];
    }

    #[DataProvider('createResolverDelegateInstanceFromClassNameDataProvider')]
    #[Test]
    public function createResolverDelegateInstanceFromClassName(string $resolverClassName, string $expectedInstanceOf): void
    {
        $subject = new ViewHelperResolver();
        $result = $subject->createResolverDelegateInstanceFromClassName($resolverClassName);
        self::assertInstanceOf($expectedInstanceOf, $result);
    }

    public static function getResponsibleDelegateDataProvider(): iterable
    {
        return [
            [['test' => ['TYPO3Fluid\\Fluid\\ViewHelpers']], ViewHelperCollection::class],
            [['test' => [TestViewHelperResolverDelegate::class]], TestViewHelperResolverDelegate::class],
            [['test' => [null, TestViewHelperResolverDelegate::class]], TestViewHelperResolverDelegate::class],
            [['test' => [TestViewHelperResolverDelegate::class, null]], TestViewHelperResolverDelegate::class],
            [['test' => ['TYPO3Fluid\\Fluid\\ViewHelpers', TestViewHelperResolverDelegate::class]], TestViewHelperResolverDelegate::class],
        ];
    }

    #[DataProvider('getResponsibleDelegateDataProvider')]
    #[Test]
    public function getResponsibleDelegate(array $namespaces, string $expectedInstanceOf): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces($namespaces);
        $result = $subject->getResponsibleDelegate('test', 'render');
        self::assertInstanceOf($expectedInstanceOf, $result);
    }
}
