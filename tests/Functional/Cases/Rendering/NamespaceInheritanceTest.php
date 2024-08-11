<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class NamespaceInheritanceTest extends AbstractFunctionalTestCase
{
    public static function namespacesAreInheritedToLayoutAndPartialsDataProvider(): array
    {
        return [
            'namespace provided via php api' => [
                '<f:layout name="NamespaceInheritanceLayout" /><f:section name="Main"><test:tagBasedTest location="Template" /></f:section>',
                ['test' => 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers'],
                '',
            ],
            // @todo this should probably not work
            'namespace provided via inline namespace declaration in template' => [
                '{namespace test=TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers}<f:layout name="NamespaceInheritanceLayout" /><f:section name="Main"><test:tagBasedTest location="Template" /></f:section>',
                [],
                '',
            ],
            // @todo this should probably not work
            'namespace provided via xml namespace declaration in template' => [
                '<html xmlns:test="http://typo3.org/ns/TYPO3Fluid/Fluid/Tests/Functional/Fixtures/ViewHelpers"><f:layout name="NamespaceInheritanceLayout" /><f:section name="Main"><test:tagBasedTest location="Template" /></f:section>',
                [],
                '',
            ],
        ];
    }

    #[Test]
    #[DataProvider('namespacesAreInheritedToLayoutAndPartialsDataProvider')]
    public function namespacesAreInheritedToLayoutAndPartials(string $source, array $predefinedNamespaces): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespaces($predefinedNamespaces);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/Layouts/']);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/../../Fixtures/Partials/']);
        self::assertSame(
            '<div location="Layout" />' . "\n\n" . '<div location="Template" />' . "\n\n" . '<div location="NestedPartial" />' . "\n\n" . '<div location="Partial" />' . "\n\n",
            $view->render(),
        );
    }
}
