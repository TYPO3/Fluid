<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class NestedFluidTemplatesWithLayoutTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function nestedTemplateRenderingWithDifferentLayoutPaths(): void
    {
        $source = '<f:layout name="Layout"/><f:section name="main"><f:format.raw>{anotherFluidTemplateContent}</f:format.raw></f:section>';
        $variables = ['anotherFluidTemplateContent' => ''];

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/Layouts/']);
        $innerView = new TemplateView();
        $innerView->getRenderingContext()->setCache(self::$cache);
        $innerView->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $innerView->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/LayoutsOverride/Layouts/']);
        $innerView->assignMultiple($variables);
        $innerOutput = $innerView->render();
        $view->assignMultiple(['anotherFluidTemplateContent' => $innerOutput]);
        $output = $view->render();
        self::assertStringContainsString('DefaultLayoutLayoutOverride', $output);

        // A second run with now compiled templates.
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/Layouts/']);
        $innerView = new TemplateView();
        $innerView->getRenderingContext()->setCache(self::$cache);
        $innerView->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $innerView->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/LayoutsOverride/Layouts/']);
        $innerView->assignMultiple($variables);
        $innerOutput = $innerView->render();
        $view->assignMultiple(['anotherFluidTemplateContent' => $innerOutput]);
        $output = $view->render();
        self::assertStringContainsString('DefaultLayoutLayoutOverride', $output);
    }
}
