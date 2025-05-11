<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ViewHelperResolverDelegateTest extends AbstractFunctionalTestCase
{
    public static function renderViewHelpersFromDelegateDataProvider(): array
    {
        return [
            'Rendering ViewHelpers from ViewHelperResolver delegate' => [
                '{namespace my=TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\TestViewHelperResolverDelegate}<my:render /> <my:render.sub />',
                'Render Render_Sub',
            ],
        ];
    }

    #[DataProvider('renderViewHelpersFromDelegateDataProvider')]
    #[Test]
    public function renderViewHelpersFromDelegate(string $source, string $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render());

        // Second run to verify cached behavior.
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render());
    }

    #[DataProvider('renderViewHelpersFromDelegateDataProvider')]
    #[Test]
    public function invalidViewHelpersFromDelegateThrowsException(): void
    {
        self::expectException(ParserException::class);
        self::expectExceptionCode(1407060572);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource(
            '{namespace my=TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\TestViewHelperResolverDelegate}<my:invalid />',
        );
        $view->render();
    }
}
