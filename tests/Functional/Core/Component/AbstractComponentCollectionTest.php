<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Component;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;
use TYPO3Fluid\Fluid\Core\Component\ComponentAdapter;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinition;
use TYPO3Fluid\Fluid\Core\Component\ComponentRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\UnresolvableViewHelperException;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\ListComponentCollection;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;
use TYPO3Fluid\Fluid\View\TemplatePaths;

final class AbstractComponentCollectionTest extends AbstractFunctionalTestCase
{
    public static function resolveTemplateNameDataProvider(): iterable
    {
        return [
            ['testComponent', 'TestComponent/TestComponent'],
            ['Testcomponent', 'Testcomponent/Testcomponent'],
            ['test.sub', 'Test/Sub/Sub'],
            ['test.sub.subsub.subsubsub', 'Test/Sub/Subsub/Subsubsub/Subsubsub'],
        ];
    }

    #[Test]
    #[DataProvider('resolveTemplateNameDataProvider')]
    public function resolveTemplateName(string $viewHelperName, string $expectedTemplateName): void
    {
        $subject = new class () extends AbstractComponentCollection {
            public function getTemplatePaths(): TemplatePaths
            {
                return new TemplatePaths();
            }
        };
        self::assertSame($expectedTemplateName, $subject->resolveTemplateName($viewHelperName));
    }

    public static function getComponentDefinitionDataProvider(): iterable
    {
        return [
            [
                'testComponent',
                new ComponentDefinition(
                    'testComponent',
                    [
                        'title' => new ArgumentDefinition('title', 'string', '', true),
                        'tags' => new ArgumentDefinition('tags', 'array', '', false),
                    ],
                    false,
                    [],
                ),
            ],
            [
                'recursive',
                new ComponentDefinition(
                    'recursive',
                    [
                        'counter' => new ArgumentDefinition('counter', 'int', '', true),
                    ],
                    false,
                    [],
                ),
            ],
            [
                'localNamespaceImport',
                new ComponentDefinition(
                    'localNamespaceImport',
                    [
                        'foo' => new ArgumentDefinition('foo', 'string', '', true),
                    ],
                    false,
                    [],
                ),
            ],
            [
                'unresolvableLocalNamespaceImport',
                new ComponentDefinition(
                    'unresolvableLocalNamespaceImport',
                    [
                        'foo' => new ArgumentDefinition('foo', 'string', '', true),
                    ],
                    false,
                    [],
                ),
            ],
            [
                'globalNamespaceUsage',
                new ComponentDefinition(
                    'globalNamespaceUsage',
                    [
                        'bar' => new ArgumentDefinition('bar', 'string', '', true),
                    ],
                    false,
                    [],
                ),
            ],
            [
                'slotComponent',
                new ComponentDefinition(
                    'slotComponent',
                    [],
                    false,
                    ['default'],
                ),
            ],
            [
                'namedSlots',
                new ComponentDefinition(
                    'namedSlots',
                    [],
                    false,
                    ['test1', 'test2', 'default'],
                ),
            ],
        ];
    }

    #[Test]
    #[DataProvider('getComponentDefinitionDataProvider')]
    public function getComponentDefinition(string $viewHelperName, ComponentDefinition $componentDefinition): void
    {
        $subject = new class () extends AbstractComponentCollection {
            public function getTemplatePaths(): TemplatePaths
            {
                $templatePaths = new TemplatePaths();
                $templatePaths->setTemplateRootPaths([__DIR__ . '/../../Fixtures/Components']);
                return $templatePaths;
            }
        };
        self::assertEquals($componentDefinition, $subject->getComponentDefinition($viewHelperName), 'uncached');
        self::assertEquals($componentDefinition, $subject->getComponentDefinition($viewHelperName), 'runtime cached');
    }

    #[Test]
    public function getComponentDefinitionThrowsException(): void
    {
        self::expectException(InvalidTemplateResourceException::class);
        $subject = new class () extends AbstractComponentCollection {
            public function getTemplatePaths(): TemplatePaths
            {
                $templatePaths = new TemplatePaths();
                $templatePaths->setTemplateRootPaths([__DIR__ . '/../../Fixtures/Components']);
                return $templatePaths;
            }
        };
        $subject->getComponentDefinition('invalid');
    }

    public static function resolveViewHelperClassNameDataProvider(): iterable
    {
        return [
            ['testComponent'],
            ['TestComponent'],
            ['recursive'],
            ['nested.subComponent'],
            ['Nested.SubComponent'],
        ];
    }

    #[Test]
    #[DataProvider('resolveViewHelperClassNameDataProvider')]
    public function resolveViewHelperClassName(string $viewHelperName): void
    {
        $subject = new class () extends AbstractComponentCollection {
            public function getTemplatePaths(): TemplatePaths
            {
                $templatePaths = new TemplatePaths();
                $templatePaths->setTemplateRootPaths([__DIR__ . '/../../Fixtures/Components']);
                return $templatePaths;
            }
        };
        self::assertSame(ComponentAdapter::class, $subject->resolveViewHelperClassName($viewHelperName));
    }

    #[Test]
    public function resolveViewHelperClassNameThrowsExceptionForUnknownComponent(): void
    {
        self::expectException(UnresolvableViewHelperException::class);
        self::expectExceptionCode(1748511297);

        $subject = new class () extends AbstractComponentCollection {
            public function getTemplatePaths(): TemplatePaths
            {
                $templatePaths = new TemplatePaths();
                $templatePaths->setTemplateRootPaths([__DIR__ . '/../../Fixtures/Components']);
                return $templatePaths;
            }
        };
        self::assertSame(ComponentAdapter::class, $subject->resolveViewHelperClassName('unknown'));
    }

    #[Test]
    public function getComponentRenderer(): void
    {
        $subject = new class () extends AbstractComponentCollection {
            public function getTemplatePaths(): TemplatePaths
            {
                return new TemplatePaths();
            }
        };
        self::assertInstanceOf(ComponentRenderer::class, $subject->getComponentRenderer());
    }

    #[Test]
    public function getNamespace(): void
    {
        $subject = new class () extends AbstractComponentCollection {
            public function getTemplatePaths(): TemplatePaths
            {
                return new TemplatePaths();
            }
        };
        self::assertSame(get_class($subject), $subject->getNamespace());
    }

    #[Test]
    public function getAvailableComponents(): void
    {
        $subject = new ListComponentCollection();
        $availableComponents = $subject->getAvailableComponents();
        sort($availableComponents);
        self::assertSame(
            [
                'additionalArguments',
                'additionalArgumentsJson',
                'additionalVariable',
                'booleanArgument',
                'enumTypeArgumentWithDefault',
                'globalNamespaceUsage',
                'localNamespaceImport',
                'namedSlots',
                'namespace.test',
                'nested.subComponent',
                'rawVariable',
                'recursive',
                'slotComponent',
                'testComponent',
                'unionTypeArgument',
                'unresolvableLocalNamespaceImport',
            ],
            $availableComponents,
        );
    }
}
