<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Parsing;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class CDataTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function cDataRemainsInTemplate(): void
    {
        $source = 'some content <![CDATA[some content within cdata]]> more content';

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        self::assertStringContainsString($source, $output);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        self::assertStringContainsString($source, $output);
    }
}
