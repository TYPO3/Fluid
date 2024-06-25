<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\Cache;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\ViewHelpers\Cache\DisableViewHelper;

final class DisableViewHelperTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function viewHelperCanBeInstantiated(): void
    {
        $subject = new DisableViewHelper();
        self::assertInstanceOf(AbstractViewHelper::class, $subject);
    }

    #[Test]
    public function templateUsingViewHelperCanBeRendered(): void
    {
        $template = '<f:cache.disable>foo</f:cache.disable>';
        $expected = 'foo';

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());
    }
}
