<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class CommentViewHelperTest extends AbstractFunctionalTestCase
{
    /**
     * @todo: That's a rather nasty side effect of f:comment. The parser
     *        still parses f:comment body, so if the body contains
     *        invalid stuff (e.g. a not closed VH tag), it explodes.
     *        The workaround is to have a CDATA wrap, as in the test set
     *        below. However, it might be possible to look into the parser
     *        regexes to see if parsing of 'f:comment' content could be
     *        suppressed somehow.
     */
    #[Test]
    public function renderThrowsExceptionWhenEncapsulatingInvalidCode(): void
    {
        $this->expectException(Exception::class);
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:comment><f:render></f:comment>');
        $view->render();
    }

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
    }

    #[DataProvider('renderDataProvider')]
    #[Test]
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
