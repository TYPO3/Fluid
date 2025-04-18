<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\ViewHelper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ViewHelperEventsTest extends AbstractFunctionalTestCase
{
    public static function eventTriggeredDataProvider(): array
    {
        return [
            ['<test:nodeInitializedEvent />', 'nodeInitializedEvent triggered'],
            ['<test:postParseEvent />', 'postParseEvent triggered'],
        ];
    }

    #[DataProvider('eventTriggeredDataProvider')]
    #[Test]
    public function eventTriggered(string $template, string $exceptionMessage): void
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage($exceptionMessage);
        $view = new TemplateView();
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        $view->render();

        // No second execution here because event only triggers for uncached templates
    }
}
