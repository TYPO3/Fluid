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
    public function addNamespaceWithStringRecordsNamespace(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('t', 'test');
        self::assertEquals(['test'], $subject->getNamespaces()['t']);
    }

    /**
     * @test
     */
    public function addNamespaceWithArrayRecordsNamespace(): void
    {
        $subject = new ViewHelperResolver();
        $subject->addNamespace('t', ['test']);
        self::assertEquals(['test'], $subject->getNamespaces()['t']);
    }

    /**
     * @test
     */
    public function setNamespacesSetsNamespaces(): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces(['t' => ['test']]);
        self::assertEquals(['t' => ['test']], $subject->getNamespaces());
    }

    /**
     * @test
     */
    public function testSetNamespacesSetsNamespacesAndConvertsStringNamespaceToArray(): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces(['t' => 'test']);
        self::assertAttributeEquals(['t' => ['test']], 'namespaces', $subject);
    }

    /**
     * @test
     */
    public function testIsNamespaceReturnsFalseIfNamespaceNotValid(): void
    {
        $subject = new ViewHelperResolver();
        $result = $subject->isNamespaceValid('test2');
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function testResolveViewHelperClassNameThrowsExceptionIfClassNotResolved(): void
    {
        $this->expectException(Exception::class);
        $subject = new ViewHelperResolver();
        $subject->resolveViewHelperClassName('f', 'invalid');
    }

    /**
     * @test
     */
    public function testResolveViewHelperNameSupportsMultipleNamespaces(): void
    {
        $subject = $this->getAccessibleMock(ViewHelperResolver::class, []);
        $subject->_set('namespaces', [
            'f' => [
                'TYPO3Fluid\\Fluid\\ViewHelpers',
                'Foo\\Bar'
            ]
        ]);
        $result = $subject->_call('resolveViewHelperName', 'f', 'render');
        self::assertEquals('TYPO3Fluid\\Fluid\\ViewHelpers\\RenderViewHelper', $result);
    }

    /**
     * @test
     */
    public function testResolveViewHelperNameDoesNotChokeOnNullInMultipleNamespaces(): void
    {
        $subject = $this->getAccessibleMock(ViewHelperResolver::class, []);
        $subject->_set('namespaces', [
            'f' => [
                'TYPO3Fluid\\Fluid\\ViewHelpers',
                null
            ]
        ]);
        $result = $subject->_call('resolveViewHelperName', 'f', 'render');
        self::assertEquals('TYPO3Fluid\\Fluid\\ViewHelpers\\RenderViewHelper', $result);
    }

    /**
     * @test
     */
    public function testResolveViewHelperNameTrimsBackslashSuffixFromNamespace(): void
    {
        $subject = $this->getAccessibleMock(ViewHelperResolver::class, []);
        $subject->_set('namespaces', ['f' => ['TYPO3Fluid\\Fluid\\ViewHelpers\\']]);
        $result = $subject->_call('resolveViewHelperName', 'f', 'render');
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
        $subject = new ViewHelperResolver();
        self::assertEquals($expected, $subject->resolvePhpNamespaceFromFluidNamespace($input));
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
        $subject = new ViewHelperResolver();
        $subject->addNamespace('foo', 'Some\Namespace');
        self::assertAttributeEquals(['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'foo' => ['Some\Namespace']], 'namespaces', $subject);
        $subject->addNamespace('foo', 'Some\Namespace');
        self::assertAttributeEquals(['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'foo' => ['Some\Namespace']], 'namespaces', $subject);
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
    public function testIsNamespaceValidOrIgnored(array $namespaces, string $namespace, bool $expected): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces($namespaces);
        $result = $subject->isNamespaceValid($namespace);
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
    public function testIsNamespaceIgnored(array $namespaces, string $namespace, bool $expected): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces($namespaces);
        $result = $subject->isNamespaceIgnored($namespace);
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
    public function testIsNamespaceValidOrIgnoredTestValues(array $namespaces, string $namespace, bool $expected): void
    {
        $subject = new ViewHelperResolver();
        $subject->setNamespaces($namespaces);
        $result = $subject->isNamespaceValidOrIgnored($namespace);
        self::assertEquals($expected, $result);
    }
}
