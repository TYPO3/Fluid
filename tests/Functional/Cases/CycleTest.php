<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class CycleTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function cycleValuesInArray(): void
    {
        $source = '<f:for each="{items}" as="item"><f:cycle values="{cycles}" as="cycled">{cycled}</f:cycle></f:for>';
        $variables = [
            'items' => [0, 1, 2, 3],
            'cycles' => ['a', 'b'],
        ];

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        self::assertStringContainsString('abab', $output);
        self::assertStringNotContainsString('aa', $output);
        self::assertStringNotContainsString('bb', $output);

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        self::assertStringContainsString('abab', $output);
        self::assertStringNotContainsString('aa', $output);
        self::assertStringNotContainsString('bb', $output);
    }
}
