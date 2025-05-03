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
            'namespace provided via php api' => [
                '<f:cache.disable /><f:layout name="NamespaceInheritanceLayout" /><f:section name="Main"><test:tagBasedTest location="Template" /></f:section>',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'test' => ['TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers']],
                [],
            ],
            // @todo this should probably not work
            'namespace provided to layout and partials via inline namespace declaration in template' => [
                '{namespace test=TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers}<f:cache.disable /><f:layout name="NamespaceInheritanceLayout" /><f:section name="Main"><test:tagBasedTest location="Template" /></f:section>',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                [],
            ],
            // @todo this should probably not work
            'namespace provided to layout and partials via xml namespace declaration in template' => [
                '<html xmlns:test="http://typo3.org/ns/TYPO3Fluid/Fluid/Tests/Functional/Fixtures/ViewHelpers"><f:cache.disable /><f:layout name="NamespaceInheritanceLayout" /><f:section name="Main"><test:tagBasedTest location="Template" /></f:section>',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                [],
            ],
            // // @todo this should probably not work
            'namespace inherited from template to dynamic layout' => [
                '<html xmlns:test="http://typo3.org/ns/TYPO3Fluid/Fluid/Tests/Functional/Fixtures/ViewHelpers"><f:cache.disable /><f:layout name="{myLayout}" /><f:section name="Main"><test:tagBasedTest location="Template" /></f:section>',
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                ['myLayout' => 'NamespaceInheritanceLayout'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('namespacesAreInheritedToLayoutAndPartialsDataProvider')]
    public function namespacesAreInheritedToLayoutAndPartials(string $source, array $initialNamespaces, array $variables): void
    {
        $expectedResult = "\n" . '<div location="Layout" />' . "\n\n" . '<div location="Template" />' . "\n\n\n\n" . '<div location="NestedPartial" />' . "\n\n" . '<div location="Partial" />' . "\n\n";

        // Uncached
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->setNamespaces($initialNamespaces);
        $view->assignMultiple($variables);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/Layouts/']);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/../../Fixtures/Partials/']);
        self::assertSame($expectedResult, $view->render());

        // @todo Cached state is currently not relevant here, since caching needs to be disabled for all affected templates to get robust testing results
        //       With enabled caching, there is interference between the test cases because the "in-memory" cache of TemplateCompiler is re-used and thus
        //       test cases might be green even if they should actually be red. See https://github.com/TYPO3/Fluid/issues/975
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
}
