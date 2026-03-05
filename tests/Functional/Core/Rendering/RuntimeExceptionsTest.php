<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Rendering;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class RuntimeExceptionsTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function originalTemplatePathIsConsistent(): void
    {
        $assertContains = [
            '|Layout before: ' . __DIR__ . '/../../Fixtures/Layouts/OriginalTemplatePathLayout.html|',
            '|Layout after: ' . __DIR__ . '/../../Fixtures/Layouts/OriginalTemplatePathLayout.html|',
            '|Template before: ' . __DIR__ . '/../../Fixtures/Templates/OriginalTemplatePath.html|',
            '|Template after: ' . __DIR__ . '/../../Fixtures/Templates/OriginalTemplatePath.html|',
            '|Template inline: ' . __DIR__ . '/../../Fixtures/Templates/OriginalTemplatePath.html|',
            '|Partial before: ' . __DIR__ . '/../../Fixtures/Partials/OriginalTemplatePathPartial.html|',
            '|Partial after: ' . __DIR__ . '/../../Fixtures/Partials/OriginalTemplatePathPartial.html|',
            '|Partial nested: ' . __DIR__ . '/../../Fixtures/Partials/OriginalTemplatePathPartialNested.html|',
        ];

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateRootPaths([__DIR__ . '/../../Fixtures/Templates/']);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/../../Fixtures/Partials/']);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/Layouts/']);
        $result = $view->render('OriginalTemplatePath');
        foreach ($assertContains as $contains) {
            self::assertStringContainsString($contains, $result, 'uncached');
        }

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateRootPaths([__DIR__ . '/../../Fixtures/Templates/']);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/../../Fixtures/Partials/']);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/Layouts/']);
        $result = $view->render('OriginalTemplatePath');
        foreach ($assertContains as $contains) {
            self::assertStringContainsString($contains, $result, 'cached');
        }
    }
}
