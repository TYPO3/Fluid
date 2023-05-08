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

class ViewHelperResolverTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testAddNamespaceWithStringRecordsNamespace(): void
    {
        $resolver = new ViewHelperResolver();
        $resolver->addNamespace('t', 'test');
        self::assertAttributeContains(['test'], 'namespaces', $resolver);
    }

    /**
     * @test
     */
    public function testAddNamespaceWithArrayRecordsNamespace(): void
    {
        $resolver = new ViewHelperResolver();
        $resolver->addNamespace('t', ['test']);
        self::assertAttributeContains(['test'], 'namespaces', $resolver);
    }

    /**
     * @test
     */
    public function testSetNamespacesSetsNamespaces(): void
    {
        $resolver = new ViewHelperResolver();
        $resolver->setNamespaces(['t' => ['test']]);
        self::assertAttributeEquals(['t' => ['test']], 'namespaces', $resolver);
    }

    /**
     * @test
     */
    public function testSetNamespacesSetsNamespacesAndConvertsStringNamespaceToArray(): void
    {
        $resolver = new ViewHelperResolver();
        $resolver->setNamespaces(['t' => 'test']);
        self::assertAttributeEquals(['t' => ['test']], 'namespaces', $resolver);
    }

    /**
     * @test
     */
    public function testIsNamespaceReturnsFalseIfNamespaceNotValid(): void
    {
        $resolver = new ViewHelperResolver();
        $result = $resolver->isNamespaceValid('test2');
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function testResolveViewHelperClassNameThrowsExceptionIfClassNotResolved(): void
    {
        $this->expectException(Exception::class);
        $resolver = new ViewHelperResolver();
        $resolver->resolveViewHelperClassName('f', 'invalid');
    }

    /**
     * @test
     */
    public function testResolveViewHelperNameSupportsMultipleNamespaces(): void
    {
        $resolver = $this->getAccessibleMock(ViewHelperResolver::class, []);
        $resolver->_set('namespaces', [
            'f' => [
                'TYPO3Fluid\\Fluid\\ViewHelpers',
                'Foo\\Bar'
            ]
        ]);
        $result = $resolver->_call('resolveViewHelperName', 'f', 'render');
        self::assertEquals('TYPO3Fluid\\Fluid\\ViewHelpers\\RenderViewHelper', $result);
    }

    /**
     * @test
     */
    public function testResolveViewHelperNameDoesNotChokeOnNullInMultipleNamespaces(): void
    {
        $resolver = $this->getAccessibleMock(ViewHelperResolver::class, []);
        $resolver->_set('namespaces', [
            'f' => [
                'TYPO3Fluid\\Fluid\\ViewHelpers',
                null
            ]
        ]);
        $result = $resolver->_call('resolveViewHelperName', 'f', 'render');
        self::assertEquals('TYPO3Fluid\\Fluid\\ViewHelpers\\RenderViewHelper', $result);
    }

    /**
     * @test
     */
    public function testResolveViewHelperNameTrimsBackslashSuffixFromNamespace(): void
    {
        $resolver = $this->getAccessibleMock(ViewHelperResolver::class, []);
        $resolver->_set('namespaces', ['f' => ['TYPO3Fluid\\Fluid\\ViewHelpers\\']]);
        $result = $resolver->_call('resolveViewHelperName', 'f', 'render');
        self::assertEquals('TYPO3Fluid\\Fluid\\ViewHelpers\\RenderViewHelper', $result);
    }

    /**
     * @test
     */
    public function testAddNamespaceWithString(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('f', 'Foo\\Bar');
        self::assertAttributeEquals([
            'f' => [
                'TYPO3Fluid\\Fluid\\ViewHelpers',
                'Foo\\Bar'
            ]
        ], 'namespaces', $subject);
    }

    /**
     * @test
     */
    public function testAddNamespaceWithArray(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('f', ['Foo\\Bar']);
        self::assertAttributeEquals([
            'f' => [
                'TYPO3Fluid\\Fluid\\ViewHelpers',
                'Foo\\Bar'
            ]
        ], 'namespaces', $subject);
    }

    /**
     * @test
     */
    public function testAddNamespaceWithNull(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('ignored', null);
        self::assertAttributeEquals(['f' => ['TYPO3Fluid\\Fluid\\ViewHelpers'], 'ignored' => null], 'namespaces', $subject);
    }

    /**
     * @test
     */
    public function testAddSecondNamespaceWithNullWithExistingNullStillIgnoresNamespace(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('ignored', null);
        $subject->addNamespace('ignored', null);
        self::assertAttributeEquals(['f' => ['TYPO3Fluid\\Fluid\\ViewHelpers'], 'ignored' => null], 'namespaces', $subject);
    }

    /**
     * @test
     */
    public function testAddSecondNamespaceWithExistingNullConvertsToNotIgnoredNamespace(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('ignored', null);
        $subject->addNamespace('ignored', ['Foo\\Bar']);
        self::assertAttributeEquals(['f' => ['TYPO3Fluid\\Fluid\\ViewHelpers'], 'ignored' => ['Foo\\Bar']], 'namespaces', $subject);
    }

    /**
     * @test
     */
    public function testAddNamespaces(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespaces(['f' => 'Foo\\Bar']);
        self::assertAttributeEquals([
            'f' => [
                'TYPO3Fluid\\Fluid\\ViewHelpers',
                'Foo\\Bar'
            ]
        ], 'namespaces', $subject);
    }

    public static function getResolvePhpNamespaceFromFluidNamespaceTestValues(): array
    {
        return [
            ['Foo\\Bar', 'Foo\\Bar\\ViewHelpers'],
            ['Foo\\Bar\\ViewHelpers', 'Foo\\Bar\\ViewHelpers'],
            ['http://typo3.org/ns/Foo/Bar/ViewHelpers', 'Foo\\Bar\\ViewHelpers'],
        ];
    }

    /**
     * @test
     * @dataProvider getResolvePhpNamespaceFromFluidNamespaceTestValues
     */
    public function testResolvePhpNamespaceFromFluidNamespace(string $input, string $expected): void
    {
        $resolver = new ViewHelperResolver();
        self::assertEquals($expected, $resolver->resolvePhpNamespaceFromFluidNamespace($input));
    }

    /**
     * @test
     */
    public function testCreateViewHelperInstance(): void
    {
        $subject = $this->getMockBuilder(ViewHelperResolver::class)
            ->onlyMethods(['resolveViewHelperClassName', 'createViewHelperInstanceFromClassName'])
            ->getMock();
        $subject->expects(self::once())->method('resolveViewHelperClassName')->with('foo', 'bar')->willReturn('foobar');
        $subject->expects(self::once())->method('createViewHelperInstanceFromClassName')->with('foobar')->willReturn('baz');
        self::assertEquals('baz', $subject->createViewHelperInstance('foo', 'bar'));
    }

    /**
     * @test
     */
    public function addNamespaceDoesNotThrowAnExceptionIfTheAliasExistAlreadyAndPointsToTheSamePhpNamespace(): void
    {
        $resolver = new ViewHelperResolver();
        $resolver->addNamespace('foo', 'Some\Namespace');
        self::assertAttributeEquals(['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'foo' => ['Some\Namespace']], 'namespaces', $resolver);
        $resolver->addNamespace('foo', 'Some\Namespace');
        self::assertAttributeEquals(['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'foo' => ['Some\Namespace']], 'namespaces', $resolver);
    }

    public static function getIsNamespaceValidTestValues(): array
    {
        return [
            [['foo' => null], 'foo', false],
            [['foo' => ['test']], 'foo', true],
            [['foo' => ['test']], 'foobar', false],
            [['foo*' => null], 'foo', false],
        ];
    }

    /**
     * @test
     * @dataProvider getIsNamespaceValidTestValues
     */
    public function testIsNamespaceValidOrIgnored(array $namespaces, string $subject, bool $expected): void
    {
        $resolver = new ViewHelperResolver();
        $resolver->setNamespaces($namespaces);
        $result = $resolver->isNamespaceValid($subject);
        self::assertEquals($expected, $result);
    }

    public static function getIsNamespaceIgnoredTestValues(): array
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
     * @dataProvider getIsNamespaceIgnoredTestValues
     */
    public function testIsNamespaceIgnored(array $namespaces, string $subject, bool $expected): void
    {
        $resolver = new ViewHelperResolver();
        $resolver->setNamespaces($namespaces);
        $result = $resolver->isNamespaceIgnored($subject);
        self::assertEquals($expected, $result);
    }

    public static function getIsNamespaceValidOrIgnoredTestValues(): array
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
     * @dataProvider getIsNamespaceValidOrIgnoredTestValues
     */
    public function testIsNamespaceValidOrIgnoredTestValues(array $namespaces, string $subject, bool $expected): void
    {
        $resolver = new ViewHelperResolver();
        $resolver->setNamespaces($namespaces);
        $result = $resolver->isNamespaceValidOrIgnored($subject);
        self::assertEquals($expected, $result);
    }
}
