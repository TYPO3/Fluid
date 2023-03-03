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
    public function testAddNamespaceWithStringRecordsNamespace()
    {
        $resolver = new ViewHelperResolver();
        $resolver->addNamespace('t', 'test');
        self::assertAttributeContains(['test'], 'namespaces', $resolver);
    }

    /**
     * @test
     */
    public function testAddNamespaceWithArrayRecordsNamespace()
    {
        $resolver = new ViewHelperResolver();
        $resolver->addNamespace('t', ['test']);
        self::assertAttributeContains(['test'], 'namespaces', $resolver);
    }

    /**
     * @test
     */
    public function testSetNamespacesSetsNamespaces()
    {
        $resolver = new ViewHelperResolver();
        $resolver->setNamespaces(['t' => ['test']]);
        self::assertAttributeEquals(['t' => ['test']], 'namespaces', $resolver);
    }

    /**
     * @test
     */
    public function testSetNamespacesSetsNamespacesAndConvertsStringNamespaceToArray()
    {
        $resolver = new ViewHelperResolver();
        $resolver->setNamespaces(['t' => 'test']);
        self::assertAttributeEquals(['t' => ['test']], 'namespaces', $resolver);
    }

    /**
     * @test
     */
    public function testIsNamespaceReturnsFalseIfNamespaceNotValid()
    {
        $resolver = new ViewHelperResolver();
        $result = $resolver->isNamespaceValid('test2');
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function testResolveViewHelperClassNameThrowsExceptionIfClassNotResolved()
    {
        $resolver = new ViewHelperResolver();
        $this->setExpectedException(Exception::class);
        $resolver->resolveViewHelperClassName('f', 'invalid');
    }

    /**
     * @test
     */
    public function testResolveViewHelperNameSupportsMultipleNamespaces()
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
    public function testResolveViewHelperNameDoesNotChokeOnNullInMultipleNamespaces()
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
    public function testResolveViewHelperNameTrimsBackslashSuffixFromNamespace()
    {
        $resolver = $this->getAccessibleMock(ViewHelperResolver::class, []);
        $resolver->_set('namespaces', ['f' => ['TYPO3Fluid\\Fluid\\ViewHelpers\\']]);
        $result = $resolver->_call('resolveViewHelperName', 'f', 'render');
        self::assertEquals('TYPO3Fluid\\Fluid\\ViewHelpers\\RenderViewHelper', $result);
    }

    /**
     * @test
     */
    public function testAddNamespaceWithString()
    {
        $resolver = $this->getMock(ViewHelperResolver::class, []);
        $resolver->addNamespace('f', 'Foo\\Bar');
        self::assertAttributeEquals([
            'f' => [
                'TYPO3Fluid\\Fluid\\ViewHelpers',
                'Foo\\Bar'
            ]
        ], 'namespaces', $resolver);
    }

    /**
     * @test
     */
    public function testAddNamespaceWithArray()
    {
        $resolver = $this->getMock(ViewHelperResolver::class, []);
        $resolver->addNamespace('f', ['Foo\\Bar']);
        self::assertAttributeEquals([
            'f' => [
                'TYPO3Fluid\\Fluid\\ViewHelpers',
                'Foo\\Bar'
            ]
        ], 'namespaces', $resolver);
    }

    /**
     * @test
     */
    public function testAddNamespaceWithNull()
    {
        $resolver = $this->getMock(ViewHelperResolver::class, []);
        $resolver->addNamespace('ignored', null);
        self::assertAttributeEquals(['f' => ['TYPO3Fluid\\Fluid\\ViewHelpers'], 'ignored' => null], 'namespaces', $resolver);
    }

    /**
     * @test
     */
    public function testAddSecondNamespaceWithNullWithExistingNullStillIgnoresNamespace()
    {
        $resolver = $this->getMock(ViewHelperResolver::class, []);
        $resolver->addNamespace('ignored', null);
        $resolver->addNamespace('ignored', null);
        self::assertAttributeEquals(['f' => ['TYPO3Fluid\\Fluid\\ViewHelpers'], 'ignored' => null], 'namespaces', $resolver);
    }

    /**
     * @test
     */
    public function testAddSecondNamespaceWithExistingNullConvertsToNotIgnoredNamespace()
    {
        $resolver = $this->getMock(ViewHelperResolver::class, []);
        $resolver->addNamespace('ignored', null);
        $resolver->addNamespace('ignored', ['Foo\\Bar']);
        self::assertAttributeEquals(['f' => ['TYPO3Fluid\\Fluid\\ViewHelpers'], 'ignored' => ['Foo\\Bar']], 'namespaces', $resolver);
    }

    /**
     * @test
     */
    public function testAddNamespaces()
    {
        $resolver = $this->getMock(ViewHelperResolver::class, []);
        $resolver->addNamespaces(['f' => 'Foo\\Bar']);
        self::assertAttributeEquals([
            'f' => [
                'TYPO3Fluid\\Fluid\\ViewHelpers',
                'Foo\\Bar'
            ]
        ], 'namespaces', $resolver);
    }

    /**
     * @param string $input
     * @param string $expected
     * @test
     * @dataProvider getResolvePhpNamespaceFromFluidNamespaceTestValues
     */
    public function testResolvePhpNamespaceFromFluidNamespace($input, $expected)
    {
        $resolver = new ViewHelperResolver();
        self::assertEquals($expected, $resolver->resolvePhpNamespaceFromFluidNamespace($input));
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
     */
    public function testCreateViewHelperInstance()
    {
        $resolver = $this->getMock(
            ViewHelperResolver::class,
            ['resolveViewHelperClassName', 'createViewHelperInstanceFromClassName']
        );
        $resolver->expects(self::once())->method('resolveViewHelperClassName')->with('foo', 'bar')->willReturn('foobar');
        $resolver->expects(self::once())->method('createViewHelperInstanceFromClassName')->with('foobar')->willReturn('baz');
        self::assertEquals('baz', $resolver->createViewHelperInstance('foo', 'bar'));
    }

    /**
     * @test
     */
    public function addNamespaceDoesNotThrowAnExceptionIfTheAliasExistAlreadyAndPointsToTheSamePhpNamespace()
    {
        $resolver = new ViewHelperResolver();
        $resolver->addNamespace('foo', 'Some\Namespace');
        self::assertAttributeEquals(['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'foo' => ['Some\Namespace']], 'namespaces', $resolver);
        $resolver->addNamespace('foo', 'Some\Namespace');
        self::assertAttributeEquals(['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'foo' => ['Some\Namespace']], 'namespaces', $resolver);
    }

    /**
     * @param array $namespaces
     * @param string $subject
     * @param bool $expected
     * @test
     * @dataProvider getIsNamespaceValidTestValues
     */
    public function testIsNamespaceValidOrIgnored(array $namespaces, $subject, $expected)
    {
        $resolver = new ViewHelperResolver();
        $resolver->setNamespaces($namespaces);
        $result = $resolver->isNamespaceValid($subject);
        self::assertEquals($expected, $result);
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
     * @param array $namespaces
     * @param string $subject
     * @param bool $expected
     * @test
     * @dataProvider getIsNamespaceIgnoredTestValues
     */
    public function testIsNamespaceIgnored(array $namespaces, $subject, $expected)
    {
        $resolver = new ViewHelperResolver();
        $resolver->setNamespaces($namespaces);
        $result = $resolver->isNamespaceIgnored($subject);
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
     * @param array $namespaces
     * @param string $subject
     * @param bool $expected
     * @test
     * @dataProvider getIsNamespaceValidOrIgnoredTestValues
     */
    public function testIsNamespaceValidOrIgnoredTestValues(array $namespaces, $subject, $expected)
    {
        $resolver = new ViewHelperResolver();
        $resolver->setNamespaces($namespaces);
        $result = $resolver->isNamespaceValidOrIgnored($subject);
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
}
