<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\StaticCacheable;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Regression test for https://github.com/TYPO3/Fluid/issues/804.
 */
final class NotSharedStaticCompilableViewHelperTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function renderWithNotSharedCompilableViewHelper(): void
    {
        $template = __DIR__ . '/Fixtures/Templates/Results.html';
        $expectedMarkup = trim(file_get_contents(__DIR__ . '/Fixtures/ExpectedOutput.html'));

        $view = new TemplateView();
        $view->assignMultiple([]);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('s', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\ViewHelpers\\StaticCacheable\\Fixtures\\ViewHelpers');
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/Fixtures/Partials/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateRootPaths([__DIR__ . '/Fixtures/Templates/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename($template);
        self::assertSame($expectedMarkup, trim($view->render()), 'Uncached (#1) run returned unexpected output');

        $view = new TemplateView();
        $view->assignMultiple([]);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('s', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\ViewHelpers\\StaticCacheable\\Fixtures\\ViewHelpers');
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/Fixtures/Partials/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateRootPaths([__DIR__ . '/Fixtures/Templates/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename($template);
        self::assertSame($expectedMarkup, trim($view->render()), 'Cached (#2) run returned unexpected output');
    }
}
