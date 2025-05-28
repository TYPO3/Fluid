<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Rendering;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class LayoutNameVariableTest extends AbstractFunctionalTestCase
{
    #[Test]
    #[IgnoreDeprecations]
    public function layoutVariable(): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getVariableProvider()->add('layoutName', 'Default');
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/Layouts/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('');
        self::assertSame('DefaultLayout with name Default.html', trim($view->render()), 'uncached');

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getVariableProvider()->add('layoutName', 'Default');
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/Layouts/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('');
        self::assertSame('DefaultLayout with name Default.html', trim($view->render()), 'cached');
    }
}
