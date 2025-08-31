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
use TYPO3Fluid\Fluid\Core\Variables\InvalidVariableIdentifierException;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Variables\Fixtures\StandardVariableProviderContainerFixture;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Variables\Fixtures\StandardVariableProviderModelFixture;

final class StandardVariableProviderTest extends TestCase
{
    #[Test]
    public function getSourceReturnsEmptyArray(): void
    {
        $subject = new StandardVariableProvider();
        self::assertSame([], $subject->getSource());
    }

    #[Test]
    public function getSourceReturnsPreviouslySetSource(): void
    {
        $subject = new StandardVariableProvider();
        $subject->setSource(['foo' => 'bar']);
        self::assertSame(['foo' => 'bar'], $subject->getSource());
    }

    #[Test]
    public function getSourceReturnsPreviouslyAddedVariables(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('name', 'Simon');
        $subject->add('org', 'TYPO3');
        self::assertSame(['name' => 'Simon', 'org' => 'TYPO3'], $subject->getSource());
    }

    #[Test]
    public function getAllReturnsEmptyArray(): void
    {
        $subject = new StandardVariableProvider();
        self::assertSame([], $subject->getAll());
    }

    #[Test]
    public function getAllReturnsPreviouslySetSource(): void
    {
        $subject = new StandardVariableProvider();
        $subject->setSource(['foo' => 'bar']);
        self::assertSame(['foo' => 'bar'], $subject->getAll());
    }

    #[Test]
    public function getAllReturnsPreviouslyAddedVariables(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('name', 'Simon');
        $subject->add('org', 'TYPO3');
        self::assertSame(['name' => 'Simon', 'org' => 'TYPO3'], $subject->getAll());
    }

    #[Test]
    public function getAllIdentifiersReturnsEmptyArray(): void
    {
        $subject = new StandardVariableProvider();
        self::assertSame([], $subject->getAllIdentifiers());
    }

    #[Test]
    public function getAllIdentifiersReturnsKeysOfPreviouslySetSource(): void
    {
        $subject = new StandardVariableProvider();
        $subject->setSource(['foo' => 'bar']);
        self::assertSame(['foo'], $subject->getAllIdentifiers());
    }

    #[Test]
    public function getAllIdentifiersReturnsKeysOfPreviouslyAddedVariables(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('name', 'Simon');
        $subject->add('org', 'TYPO3');
        self::assertSame(['name', 'org'], $subject->getAllIdentifiers());
    }

    #[Test]
    public function getReturnsNullWithNotExistingVariable(): void
    {
        $variableProvider = new StandardVariableProvider();
        self::assertNull($variableProvider->get('nonexistent'));
    }

    #[Test]
    public function getReturnsPreviouslyAddedVariable(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('variable', 'someString');
        self::assertSame($subject->get('variable'), 'someString');
    }

    #[Test]
    public function getAsArrayAccessReturnsPreviouslySetVariable(): void
    {
        $subject = new StandardVariableProvider();
        $subject['variable'] = 'someString';
        self::assertSame($subject['variable'], 'someString');
    }

    #[Test]
    public function existsReturnsTrueWithPreviouslyAddedVariable(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('variable', 'someString');
        self::assertTrue($subject->exists('variable'));
    }

    #[Test]
    public function existsAsArrayAccessReturnsTrueWithPreviouslyAddedVariable(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('variable', 'someString');
        self::assertTrue(isset($subject['variable']));
    }

    #[Test]
    public function unsetAsArrayAccessRemovesVariable(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('variable', 'test');
        unset($subject['variable']);
        self::assertFalse($subject->exists('variable'));
    }

    #[Test]
    public function removeReallyRemovesVariables(): void
    {
        $subject = new StandardVariableProvider();
        $subject->add('variable', 'string1');
        $subject->remove('variable');
        self::assertNull($subject->get('variable'));
    }

    #[Test]
    public function sleepReturnsArrayWithVariableKey(): void
    {
        $subject = new StandardVariableProvider();
        $properties = $subject->__sleep();
        self::assertContains('variables', $properties);
    }

    #[Test]
    public function getScopeCopyReturnsCopyWithSettings(): void
    {
        $subject = new StandardVariableProvider(['foo' => 'bar', 'settings' => ['baz' => 'bam']]);
        $copy = $subject->getScopeCopy(['bar' => 'foo']);
        self::assertSame(['bar' => 'foo', 'settings' => ['baz' => 'bam']], $copy->getAll());
    }

    #[Test]
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
                    'foo' => 'bar',
                ],
                'foo',
                'bar',
            ],
            'access not existing sub array on string value returns null' => [
                [
                    'foo' => 'bar',
                ],
                'foo.invalid',
                null,
            ],
            'access object getter' => [
                [
                    'user' => new StandardVariableProviderModelFixture('Foobar Name'),
                ],
                'user.name',
                'Foobar Name',
            ],
            'access object getter that returns empty string' => [
                [
                    'user' => new StandardVariableProviderModelFixture(''),
                ],
                'user.name',
                '',
            ],
            'access object isser' => [
                [
                    'user' => new StandardVariableProviderModelFixture('Foobar Name'),
                ],
                'user.named',
                true,
            ],
            'access object isser that returns false' => [
                [
                    'user' => new StandardVariableProviderModelFixture(''),
                ],
                'user.named',
                false,
            ],
            'access object hasser' => [
                [
                    'user' => new StandardVariableProviderModelFixture('Foobar Name'),
                ],
                'user.someName',
                true,
            ],
            'access object hasser that returns false' => [
                [
                    'user' => new StandardVariableProviderModelFixture(''),
                ],
                'user.someName',
                false,
            ],
            'access public object property' => [
                [
                    'user' => new StandardVariableProviderModelFixture(''),
                ],
                'user.existingPublicProperty',
                'existingPublicPropertyValue',
            ],
            'access not existing object detail returns null' => [
                [
                    'user' => new StandardVariableProviderModelFixture(''),
                ],
                'user.invalid',
                null,
            ],
            'access container' => [
                [
                    'user' => new StandardVariableProviderContainerFixture(['name' => 'Foobar Name']),
                ],
                'user.name',
                'Foobar Name',
            ],
            'access container getter that returns empty string' => [
                [
                    'user' => new StandardVariableProviderContainerFixture(['name' => '']),
                ],
                'user.name',
                '',
            ],
            'access container getter that returns FALSE' => [
                [
                    'user' => new StandardVariableProviderContainerFixture(['name' => false]),
                ],
                'user.name',
                false,
            ],
            'access container getter that returns object' => [
                [
                    'user' => new StandardVariableProviderContainerFixture(['object' => new \stdClass()]),
                ],
                'user.object',
                new \stdClass(),
            ],
            'access container getter that returns object recursive' => [
                [
                    'user' => new StandardVariableProviderContainerFixture(['object' => new StandardVariableProviderModelFixture('Foobar Name')]),
                ],
                'user.object.name',
                'Foobar Name',
            ],
            'access container getter that returns container recursive' => [
                [
                    'user' => new StandardVariableProviderContainerFixture(['object' => new StandardVariableProviderContainerFixture(['name' => 'Foobar Name'])]),
                ],
                'user.object.name',
                'Foobar Name',
            ],
            'access container getter that returns array' => [
                [
                    'user' => new StandardVariableProviderContainerFixture(['array' => ['foo' => 'bar']]),
                ],
                'user.array',
                ['foo' => 'bar'],
            ],
            'access container getter that returns value of array' => [
                [
                    'user' => new StandardVariableProviderContainerFixture(['array' => ['foo' => 'bar']]),
                ],
                'user.array.foo',
                'bar',
            ],
            'access container not existing returns null' => [
                [
                    'user' => new StandardVariableProviderContainerFixture(['name' => 'Foobar Name']),
                ],
                'user.invalid',
                null,
            ],
            'access dynamic variable using invalid variable reference' => [
                [],
                '{invalid}',
                null,
            ],
            'access dynamic variable using invalid sub variable reference' => [
                [],
                '{{invalid}}',
                null,
            ],
            'access dynamic variable using invalid variable reference in string' => [
                [],
                'foo{invalid}bar',
                null,
            ],
            'access dynamic variable using invalid sub variable reference in string' => [
                [],
                'foo{{invalid}}bar',
                null,
            ],
            'access dynamic variable using invalid variable reference in dotted string' => [
                [],
                'foo.{invalid}.bar',
                null,
            ],
            'access dynamic variable using invalid sub variable reference in dotted string' => [
                [],
                'foo.{{invalid}}.bar',
                null,
            ],
            'access dynamic variable using variable reference' => [
                [
                    'foodynamicbar' => 'test',
                    'dyn' => 'dynamic',
                ],
                'foo{dyn}bar',
                'test',
            ],
            'access dynamic variable using variable reference that resolves to zero' => [
                [
                    'foo' => [
                        0 => 'bar',
                    ],
                    'dynamic' => 0,
                ],
                'foo.{dynamic}',
                'bar',
            ],
            'access dynamic variable with dotted path using variable reference' => [
                [
                    'foo' => [
                        'dynamic' => [
                            'bar' => 'test',
                        ],
                    ],
                    'dyn' => 'dynamic',
                ],
                'foo.{dyn}.bar',
                'test',
            ],
            'access nested dynamic variable with dotted path using double sub variable reference' => [
                [
                    'foo' => [
                        'sub2' => 'test',
                    ],
                    'baz' => 'sub1',
                    'sub1' => 'sub2',
                ],
                'foo.{{baz}}',
                'test',
            ],
            'access nested dynamic variable with dotted path using triple sub variable reference' => [
                [
                    'foo' => [
                        'sub3' => 'test',
                    ],
                    'baz' => 'sub1',
                    'sub1' => 'sub2',
                    'sub2' => 'sub3',
                ],
                'foo.{{{baz}}}',
                'test',
            ],
            'access nested dynamic variable with dotted path using sub variable reference' => [
                [
                    'foo' => [
                        'bar' => 'test',
                    ],
                    'dynamic' => [
                        'sub' => 'bar',
                    ],
                    'baz' => 'sub',
                ],
                'foo.{dynamic.{baz}}',
                'test',
            ],
            'access nested dynamic variable with dotted path using sub variable reference and suffix' => [
                [
                    'foo' => [
                        'bar' => [
                            'baz' => 'test',
                        ],
                    ],
                    'dynamic' => [
                        'dyn1' => 'bar',
                    ],
                    'baz' => 'dyn1',
                ],
                'foo.{dynamic.{baz}}.baz',
                'test',
            ],
            'access nested dynamic variable with dotted path using sub variable reference and double suffix' => [
                [
                    'foo' => [
                        'bar' => [
                            'baz' => 'test',
                        ],
                    ],
                    'dynamic' => [
                        'dyn1foo' => 'bar',
                    ],
                    'baz' => 'dyn1',
                ],
                'foo.{dynamic.{baz}foo}.baz',
                'test',
            ],
            'access with multiple dynamic variables' => [
                [
                    'foodynamic1dynamic2' => 'test',
                    'dyn1' => 'dynamic1',
                    'dyn2' => 'dynamic2',
                ],
                'foo{dyn1}{dyn2}',
                'test',
            ],
            'access with multiple dynamic variables and dotted path' => [
                [
                    'foo' => [
                        'dynamic1' => [
                            'dynamic2' => [
                                'bar' => 'test',
                            ],
                        ],
                    ],
                    'dyn1' => 'dynamic1',
                    'dyn2' => 'dynamic2',
                ],
                'foo.{dyn1}.{dyn2}.bar',
                'test',
            ],
            'access with multiple nested dynamic variables and dotted path' => [
                [
                    'foo' => [
                        'dynamic1-1' => [
                            'dynamic3-1dynamic4-1' => [
                                'dynamic5-1' => [
                                    'bar' => 'test',
                                ],
                            ],
                        ],
                    ],
                    'dyn1' => [
                        'dyn1-1' => 'dynamic1-1',
                    ],
                    'dyn2' => 'dyn1-1',
                    'dyn3' => 'dynamic3-1',
                    'dyn4' => 'dynamic4-1',
                    'dyn5' => [
                        'dyn5-1' => 'dynamic5-1',
                    ],
                    'dyn6' => 'dyn5-1',
                ],
                'foo.{dyn1.{dyn2}}.{dyn3}{dyn4}.{dyn5.{dyn6}}.bar',
                'test',
            ],
        ];
    }

    /**
     * @param mixed $expected
     */
    #[DataProvider('getPathTestValues')]
    #[Test]
    public function getByPathReturnsExpectedValues(array $variables, string $path, $expected): void
    {
        $subject = new StandardVariableProvider();
        $subject->setSource($variables);
        $result = $subject->getByPath($path);
        self::assertEquals($expected, $result);
    }

    public static function exceptionIsThrownForInvalidVariableIdentifierDataProvider(): array
    {
        return [
            ['true', 1723131119],
            ['TrUe', 1723131119],
            ['false', 1723131119],
            ['null', 1723131119],
            ['_all', 1723131119],
            ['_somethingElse', 1756622558],
        ];
    }

    #[Test]
    #[DataProvider('exceptionIsThrownForInvalidVariableIdentifierDataProvider')]
    public function exceptionIsThrownForInvalidVariableIdentifier(string $identifier, int $exceptionCode): void
    {
        self::expectException(InvalidVariableIdentifierException::class);
        self::expectExceptionCode($exceptionCode);
        $subject = new StandardVariableProvider();
        $subject->add($identifier, false);
    }
}
