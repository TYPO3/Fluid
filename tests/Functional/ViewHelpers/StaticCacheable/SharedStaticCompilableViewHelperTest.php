<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\StaticCacheable;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\StaticCacheable\Fixtures\ViewHelpers\CompilableViewHelper;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Regression test for https://github.com/TYPO3/Fluid/issues/804.
 */
final class SharedStaticCompilableViewHelperTest extends AbstractFunctionalTestCase
{
    #[Test]
    #[IgnoreDeprecations]
    public function renderWithSharedCompilableViewHelper(): void
    {
        // TYPO3 implements a custom ViewHelperResolver to provide DI-able ViewHelper instances. This allows
        // developers to use shared ViewHelpers by mark them as `shared` in the configuration. The following
        // mocked ViewHelperResolver simulates a simplified version of the TYPO3 implementation. We use this
        // to tests with a reused (shared) ViewHelper. See https://github.com/TYPO3/Fluid/issues/804.
        // @todo Consider to convert this into a fixture class to test other scenario's with shared ViewHelpers.
        $viewHelperResolver = new class () extends ViewHelperResolver {
            public array $sharedInstances = [];
            protected array $classesFlaggedAsShared = [
                CompilableViewHelper::class,
            ];
            public array $called = [];

            public function createViewHelperInstanceFromClassName(string $viewHelperClassName): ViewHelperInterface
            {
                $this->called[$viewHelperClassName] ??= 0;
                $this->called[$viewHelperClassName]++;

                if ($this->sharedInstances[$viewHelperClassName] ?? false) {
                    return $this->sharedInstances[$viewHelperClassName];
                }
                if (!in_array($viewHelperClassName, $this->classesFlaggedAsShared, true)) {
                    return parent::createViewHelperInstanceFromClassName($viewHelperClassName);
                }

                $this->sharedInstances[$viewHelperClassName] = parent::createViewHelperInstanceFromClassName($viewHelperClassName);
                return $this->sharedInstances[$viewHelperClassName];
            }
        };

        $template = __DIR__ . '/Fixtures/Templates/Results.html';
        $expectedMarkup = trim(file_get_contents(__DIR__ . '/Fixtures/ExpectedOutput.html'));

        $view = new TemplateView();
        $view->assignMultiple([]);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->setViewHelperResolver($viewHelperResolver);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('s', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\ViewHelpers\\StaticCacheable\\Fixtures\\ViewHelpers');
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/Fixtures/Partials/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateRootPaths([__DIR__ . '/Fixtures/Templates/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename($template);
        self::assertSame($expectedMarkup, trim($view->render()), 'Uncached (#1) run returned unexpected output');

        $view = new TemplateView();
        $view->assignMultiple([]);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->setViewHelperResolver($viewHelperResolver);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('s', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\ViewHelpers\\StaticCacheable\\Fixtures\\ViewHelpers');
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/Fixtures/Partials/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateRootPaths([__DIR__ . '/Fixtures/Templates/']);
        $view->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename($template);
        self::assertSame($expectedMarkup, trim($view->render()), 'Cached (#2) run returned unexpected output');
    }
}
