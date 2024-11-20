<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\Cache;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class WarmupViewHelperTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function templateUsingViewHelperCanBeRendered(): void
    {
        $template = '<f:cache.warmup variables="{name: \'bar\'}">' .
            '<f:render section="{name}"/>' .
            '</f:cache.warmup>' .
            '<f:section name="foo">foo section content</f:section>' .
            '<f:section name="bar">bar section content</f:section>';
        $expected = 'foo section content';

        $view = new TemplateView();
        $view->assignMultiple(['name' => 'foo']);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->assignMultiple(['name' => 'foo']);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());
    }
}
