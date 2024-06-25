<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\StaticCacheable;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
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
        // @todo If the test with the mocked ViewHelperResolver is executed standalone, it fails and shows the broken
        //       state. As soon as it is run next to other tests, it succeeds - which is currently wrong. We need to
        //       tackle this first, so we can have proper test coverage for this regression - and before covering or
        //       fixing the regression. If the skip is removed, the other test is green (which should be red right now).
        // Note: To investigate this, the method code in ViewHelperNode->updateViewHelperNodeInViewHelper() should be
        //       commented out and full test suite run vs the single concrete testcase to see if the single-failure vs
        //       succeed with full suite can be solved.
        //       Single TestCase: tests/Functional/ViewHelpers/StaticCacheable/SharedStaticCompilableViewHelperTest.php
        self::markTestSkipped('Interfering with StaticCompilableViewHelperTest::renderWithSharedCompilableViewHelper');

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
