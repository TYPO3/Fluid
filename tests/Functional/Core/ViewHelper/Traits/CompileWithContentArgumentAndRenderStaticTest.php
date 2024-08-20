<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\ViewHelper\Traits;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Core\ViewHelper\Traits\Fixtures\CompileWithContentArgumentAndRenderStaticTestTraitViewHelper;

final class CompileWithContentArgumentAndRenderStaticTest extends AbstractFunctionalTestCase
{
    #[Test]
    #[IgnoreDeprecations]
    public function compileWithContentArgumentAndRenderStatic(string $source, array $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $output = $view->render();
        foreach ($expected as $expectedValue) {
            self::assertStringContainsString($expectedValue, $output);
        }

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $output = $view->render();
        foreach ($expected as $expectedValue) {
            self::assertStringContainsString($expectedValue, $output);
        }
    }

    #[Test]
    #[IgnoreDeprecations]
    public function resolveContentArgumentNameThrowsExceptionIfNoArgumentsAvailable(): void
    {
        $this->expectException(Exception::class);
        $instance = new CompileWithContentArgumentAndRenderStaticTestTraitViewHelper();
        $instance->resolveContentArgumentName();
    }
}
