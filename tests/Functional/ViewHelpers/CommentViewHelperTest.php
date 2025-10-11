<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class CommentViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): \Generator
    {
        yield 'no output as self closing tag' => [
            '<f:comment />',
            '',
        ];
        yield 'no output when encapsulating something' => [
            '<f:comment>notRendered</f:comment>',
            '',
        ];
        yield 'before and after content is rendered' => [
            'renderedBefore<f:comment>notRendered</f:comment>renderedAfter',
            'renderedBeforerenderedAfter',
        ];
        yield 'does not choke with not closed tag wrapped in CDATA' => [
            '<f:comment><![CDATA[<f:render>]]></f:comment>',
            '',
        ];
        yield 'does not choke with invalid fluid syntax' => [
            '<f:comment><f:if>not properly closed</f:comment>',
            '',
        ];
        yield 'does not choke with invalid fluid ViewHelper' => [
            '<f:comment><my:nonexistentViewHelper /></f:comment>',
            '',
        ];
    }

    #[DataProvider('renderDataProvider')]
    #[Test]
    #[IgnoreDeprecations]
    public function render(string $template, $expected): void
    {
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
