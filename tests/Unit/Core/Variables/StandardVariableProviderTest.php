<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Variables;

use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\StandardVariableProviderModelFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class StandardVariableProviderTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getSourceReturnsEmptyArray(): void
    {
        $subject = new StandardVariableProvider();
        self::assertSame([], $subject->getSource());
    }

    /**
     * @test
     */
    public function getSourceReturnsPreviouslySetSource(): void
    {
        $subject = new StandardVariableProvider();
        $subject->setSource(['foo' => 'bar']);
        self::assertSame(['foo' => 'bar'], $subject->getSource());
    }

    /**
     * @test
     */
    public function getSourceReturnsPreviouslyAddedVariables(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('name', 'Simon');
        $subject->add('org', 'TYPO3');
        self::assertSame(['name' => 'Simon', 'org' => 'TYPO3'], $subject->getSource());
    }

    /**
     * @test
     */
    public function getAllReturnsEmptyArray(): void
    {
        $subject = new StandardVariableProvider();
        self::assertSame([], $subject->getAll());
    }

    /**
     * @test
     */
    public function getAllReturnsPreviouslySetSource(): void
    {
        $subject = new StandardVariableProvider();
        $subject->setSource(['foo' => 'bar']);
        self::assertSame(['foo' => 'bar'], $subject->getAll());
    }

    /**
     * @test
     */
    public function getAllReturnsPreviouslyAddedVariables(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('name', 'Simon');
        $subject->add('org', 'TYPO3');
        self::assertSame(['name' => 'Simon', 'org' => 'TYPO3'], $subject->getAll());
    }

    /**
     * @test
     */
    public function getAllIdentifiersReturnsEmptyArray(): void
    {
        $subject = new StandardVariableProvider();
        self::assertSame([], $subject->getAllIdentifiers());
    }

    /**
     * @test
     */
    public function getAllIdentifiersReturnsKeysOfPreviouslySetSource(): void
    {
        $subject = new StandardVariableProvider();
        $subject->setSource(['foo' => 'bar']);
        self::assertSame(['foo'], $subject->getAllIdentifiers());
    }

    /**
     * @test
     */
    public function getAllIdentifiersReturnsKeysOfPreviouslyAddedVariables(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('name', 'Simon');
        $subject->add('org', 'TYPO3');
        self::assertSame(['name', 'org'], $subject->getAllIdentifiers());
    }

    /**
     * @test
     */
    public function getReturnsNullWithNotExistingVariable(): void
    {
        $variableProvider = new StandardVariableProvider();
        self::assertNull($variableProvider->get('nonexistent'));
    }

    /**
     * @test
     */
    public function getReturnsPreviouslyAddedVariable(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('variable', 'someString');
        self::assertSame($subject->get('variable'), 'someString');
    }

    /**
     * @test
     */
    public function getAsArrayAccessReturnsPreviouslySetVariable(): void
    {
        $subject = new StandardVariableProvider();
        $subject['variable'] = 'someString';
        self::assertSame($subject['variable'], 'someString');
    }

    /**
     * @test
     */
    public function existsReturnsTrueWithPreviouslyAddedVariable(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('variable', 'someString');
        self::assertTrue($subject->exists('variable'));
    }

    /**
     * @test
     */
    public function existsAsArrayAccessReturnsTrueWithPreviouslyAddedVariable(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('variable', 'someString');
        self::assertTrue(isset($subject['variable']));
    }

    /**
     * @test
     */
    public function unsetAsArrayAccessRemovesVariable(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('variable', 'test');
        unset($subject['variable']);
        self::assertFalse($subject->exists('variable'));
    }

    /**
     * @test
     */
    public function removeReallyRemovesVariables(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('variable', 'string1');
        $subject->remove('variable');
        self::assertNull($subject->get('variable'));
    }

    /**
     * @test
     */
    public function sleepReturnsArrayWithVariableKey(): void
    {
        $subject = new StandardVariableProvider();
        $properties = $subject->__sleep();
        self::assertContains('variables', $properties);
    }

    /**
     * @test
     */
    public function getScopeCopyReturnsCopyWithSettings(): void
    {
        $subject = new StandardVariableProvider(['foo' => 'bar', 'settings' => ['baz' => 'bam']]);
        $copy = $subject->getScopeCopy(['bar' => 'foo']);
        self::assertAttributeEquals(['settings' => ['baz' => 'bam'], 'bar' => 'foo'], 'variables', $copy);
    }

    /**
     * @test
     */
    public function testSupportsDottedPath(): void
    {
        $provider = new StandardVariableProvider();
        $provider->setSource(['foo' => ['bar' => 'baz']]);
        $result = $provider->getByPath('foo.bar');
        self::assertEquals('baz', $result);
    }

    public static function getPathTestValues(): array
    {
        return [
            'access string variable' => [
                [
                    'foo' => 'bar'
                ],
                'foo',
                'bar'
            ],
            'access not existing sub array on string value returns null' => [
                [
                    'foo' => 'bar'
                ],
                'foo.invalid',
                null
            ],
            'access object getter' => [
                [
                    'user' => new StandardVariableProviderModelFixture('Foobar Name')
                ],
                'user.name',
                'Foobar Name'
            ],
            'access object getter that returns empty string' => [
                [
                    'user' => new StandardVariableProviderModelFixture('')
                ],
                'user.name',
                ''
            ],
            'access object isser' => [
                [
                    'user' => new StandardVariableProviderModelFixture('Foobar Name')
                ],
                'user.named',
                true
            ],
            'access object isser that returns false' => [
                [
                    'user' => new StandardVariableProviderModelFixture('')
                ],
                'user.named',
                false
            ],
            'access object hasser' => [
                [
                    'user' => new StandardVariableProviderModelFixture('Foobar Name')
                ],
                'user.someName',
                true
            ],
            'access object hasser that returns false' => [
                [
                    'user' => new StandardVariableProviderModelFixture('')
                ],
                'user.someName',
                false
            ],
            'access public object property' => [
                [
                    'user' => new StandardVariableProviderModelFixture('')
                ],
                'user.existingPublicProperty',
                'existingPublicPropertyValue'
            ],
            'access not existing object detail returns null' => [
                [
                    'user' => new StandardVariableProviderModelFixture('')
                ],
                'user.invalid',
                null
            ],
            'access dynamic variable using invalid variable reference' => [
                [],
                '{invalid}',
                null
            ],
            'access dynamic variable using invalid sub variable reference' => [
                [],
                '{{invalid}}',
                null
            ],
            'access dynamic variable using invalid variable reference in string' => [
                [],
                'foo{invalid}bar',
                null
            ],
            'access dynamic variable using invalid sub variable reference in string' => [
                [],
                'foo{{invalid}}bar',
                null
            ],
            'access dynamic variable using invalid variable reference in dotted string' => [
                [],
                'foo.{invalid}.bar',
                null
            ],
            'access dynamic variable using invalid sub variable reference in dotted string' => [
                [],
                'foo.{{invalid}}.bar',
                null
            ],
            'access dynamic variable using variable reference' => [
                [
                    'foodynamicbar' => 'test',
                    'dyn' => 'dynamic'
                ],
                'foo{dyn}bar',
                'test'
            ],
            'access dynamic variable with dotted path using variable reference' => [
                [
                    'foo' => [
                        'dynamic' => [
                            'bar' => 'test'
                        ]
                    ],
                    'dyn' => 'dynamic'
                ],
                'foo.{dyn}.bar',
                'test'
            ],
            'access nested dynamic variable with dotted path using sub variable reference' => [
                [
                    'foo' => [
                        'bar' => 'test'
                    ],
                    'dynamic' => [
                        'sub' => 'bar'
                    ],
                    'baz' => 'sub'
                ],
                'foo.{dynamic.{baz}}',
                'test'
            ],
        ];
    }

    /**
     * @param mixed $expected
     * @test
     * @dataProvider getPathTestValues
     */
    public function getByPathReturnsExpectedValues(array $variables, string $path, $expected): void
    {
        $subject = new StandardVariableProvider();
        $subject->setSource($variables);
        $result = $subject->getByPath($path);
        self::assertEquals($expected, $result);
    }
}
