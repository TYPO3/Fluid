<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Rendering;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Parser\UnknownNamespaceException;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class NamespaceInheritanceTest extends AbstractFunctionalTestCase
{
    public static function namespacesAreInheritedToLayoutAndPartialsDataProvider(): array
    {
        return [
            'namespace provided via php api, call viewhelper from layout' => [
                '<f:layout name="NamespaceInheritance/CallViewHelper" />',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                [],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="Layout" />',
            ],
            'namespace provided via php api, call section from layout' => [
                '<f:layout name="NamespaceInheritance/CallSection" /><f:section name="Main"><test:tagBasedTest location="Template" /></f:section>',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                [],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="Template" />',
            ],
            'namespace provided via php api, call partial from layout' => [
                '<f:layout name="NamespaceInheritance/CallPartial" />',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                [],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="NestedPartial" />' . "\n\n" . '<div location="Partial" />',
            ],
            'namespace provided via php api, call section with partial from layout' => [
                '<f:layout name="NamespaceInheritance/CallSection" /><f:section name="Main"><f:render partial="NamespaceInheritancePartial" /></f:section>',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                [],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="NestedPartial" />' . "\n\n" . '<div location="Partial" />',
            ],

            // @todo the following cases should probably not work
            'namespace provided via inline namespace declaration in template, call viewhelper from layout' => [
                '{namespace test=TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers}<f:layout name="NamespaceInheritance/CallViewHelper" />',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                [],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="Layout" />',
            ],
            'namespace provided via inline namespace declaration in template, call section from layout' => [
                '{namespace test=TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers}<f:layout name="NamespaceInheritance/CallSection" /><f:section name="Main"><test:tagBasedTest location="Template" /></f:section>',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                [],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="Template" />',
            ],
            'namespace provided via inline namespace declaration in template, call partial from layout' => [
                '{namespace test=TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers}<f:layout name="NamespaceInheritance/CallPartial" />',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                [],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="NestedPartial" />' . "\n\n" . '<div location="Partial" />',
            ],
            'namespace provided via inline namespace declaration in template, call section with partial from layout' => [
                '{namespace test=TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers}<f:layout name="NamespaceInheritance/CallSection" /><f:section name="Main"><f:render partial="NamespaceInheritancePartial" /></f:section>',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                [],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="NestedPartial" />' . "\n\n" . '<div location="Partial" />',
            ],

            // @todo the following cases should probably not work
            'namespace provided via xml namespace declaration in template, call viewhelper from layout' => [
                '<html xmlns:test="http://typo3.org/ns/TYPO3Fluid/Fluid/Tests/Functional/Fixtures/ViewHelpers" data-namespace-typo3-fluid="true"><f:layout name="NamespaceInheritance/CallViewHelper" />',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                [],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="Layout" />',
            ],
            'namespace provided via xml namespace declaration in template, call section from layout' => [
                '<html xmlns:test="http://typo3.org/ns/TYPO3Fluid/Fluid/Tests/Functional/Fixtures/ViewHelpers" data-namespace-typo3-fluid="true"><f:layout name="NamespaceInheritance/CallSection" /><f:section name="Main"><test:tagBasedTest location="Template" /></f:section>',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                [],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="Template" />',
            ],
            'namespace provided via xml namespace declaration in template, call partial from layout' => [
                '<html xmlns:test="http://typo3.org/ns/TYPO3Fluid/Fluid/Tests/Functional/Fixtures/ViewHelpers" data-namespace-typo3-fluid="true"><f:layout name="NamespaceInheritance/CallPartial" />',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                [],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="NestedPartial" />' . "\n\n" . '<div location="Partial" />',
            ],
            'namespace provided via xml namespace declaration in template, call section with partial from layout' => [
                '<html xmlns:test="http://typo3.org/ns/TYPO3Fluid/Fluid/Tests/Functional/Fixtures/ViewHelpers" data-namespace-typo3-fluid="true"><f:layout name="NamespaceInheritance/CallSection" /><f:section name="Main"><f:render partial="NamespaceInheritancePartial" /></f:section>',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                [],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="NestedPartial" />' . "\n\n" . '<div location="Partial" />',
            ],

            // @todo the following cases should probably not work
            'dynamic layout name in template, call viewhelper from layout' => [
                '{namespace test=TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers}<f:layout name="{myLayout}" />',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                ['myLayout' => 'NamespaceInheritance/CallViewHelper'],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="Layout" />',
            ],
            'dynamic layout name in template, call section from layout' => [
                '{namespace test=TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers}<f:layout name="{myLayout}" /><f:section name="Main"><test:tagBasedTest location="Template" /></f:section>',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                ['myLayout' => 'NamespaceInheritance/CallSection'],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="Template" />',
            ],
            'dynamic layout name in template, call partial from layout' => [
                '{namespace test=TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers}<f:layout name="{myLayout}" />',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                ['myLayout' => 'NamespaceInheritance/CallPartial'],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="NestedPartial" />' . "\n\n" . '<div location="Partial" />',
            ],
            'dynamic layout name in template, call section with partial from layout' => [
                '{namespace test=TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers}<f:layout name="{myLayout}" /><f:section name="Main"><f:render partial="NamespaceInheritancePartial" /></f:section>',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                ['myLayout' => 'NamespaceInheritance/CallSection'],
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                '<div location="NestedPartial" />' . "\n\n" . '<div location="Partial" />',
            ],
        ];
    }

    #[Test]
    #[DataProvider('namespacesAreInheritedToLayoutAndPartialsDataProvider')]
    public function namespacesAreInheritedToLayoutAndPartials(string $source, array $initialNamespaces, array $variables, array $expectedNamespaces, string $expectedResult): void
    {
        // Uncached
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->setNamespaces($initialNamespaces);
        $view->assignMultiple($variables);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/Layouts/']);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/../../Fixtures/Partials/']);
        // self::assertSame($expectedNamespaces, $view->getRenderingContext()->getViewHelperResolver()->getNamespaces(), 'uncached');
        self::assertSame($expectedResult, trim($view->render()), 'uncached');

        // Cached
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->setNamespaces($initialNamespaces);
        $view->assignMultiple($variables);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/Layouts/']);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/../../Fixtures/Partials/']);
        // self::assertSame($expectedNamespaces, $view->getRenderingContext()->getViewHelperResolver()->getNamespaces(), 'cached');
        // @todo Rendering result might still be inconsistent here.
        //       With enabled caching, there is interference between the test cases because the "in-memory" cache of TemplateCompiler is re-used and thus
        //       test cases might be green even if they should actually be red. See https://github.com/TYPO3/Fluid/issues/975
        self::assertSame($expectedResult, trim($view->render()), 'cached');
    }

    public static function namespaceDefinedInParentNotValidInChildrenDataProvider(): array
    {
        return [
            'namespace provided via namespace declaration in layout' => [
                '<f:layout name="DefineNamespaceLayout" /><f:section name="Main"><test:tagBasedTest location="Template" /></f:section>',
                [],
            ],
            'namespace provided via namespace declaration in variable layout' => [
                '<f:layout name="{myLayout}" /><f:section name="Main"><test:tagBasedTest location="Template" /></f:section>',
                ['myLayout' => 'DefineNamespaceLayout'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('namespaceDefinedInParentNotValidInChildrenDataProvider')]
    public function namespaceDefinedInParentNotValidInChildren(string $source, array $variables): void
    {
        self::expectException(UnknownNamespaceException::class);
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->assignMultiple($variables);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/Layouts/']);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/../../Fixtures/Partials/']);
        $view->render();
    }

    #[Test]
    #[DataProvider('namespaceDefinedInParentNotValidInChildrenDataProvider')]
    public function namespaceDefinedInParentNotValidInChildrenInCachedTemplates(string $source, array $variables): void
    {
        self::expectException(UnknownNamespaceException::class);

        // Uncached
        try {
            $view = new TemplateView();
            $view->getRenderingContext()->setCache(self::$cache);
            $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
            $view->assignMultiple($variables);
            $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/Layouts/']);
            $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/../../Fixtures/Partials/']);
            $view->render();
        } catch (UnknownNamespaceException) {
        }

        // Cached
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->assignMultiple($variables);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/Layouts/']);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/../../Fixtures/Partials/']);
        $view->render();
    }

    #[Test]
    public function namespaceDefinedDuringCompilationNotRendering(): void
    {
        $source = '<f:format.case value="test" mode="upper" />';
        $expected = 'TEST';

        // Uncached with f namespace
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render(), 'uncached');

        // Cached without f namespace
        $view = new TemplateView();
        $view->getRenderingContext()->getViewHelperResolver()->setNamespaces([]);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render(), 'cached');
    }
}
