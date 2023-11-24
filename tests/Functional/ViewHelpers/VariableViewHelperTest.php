<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class VariableViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): \Generator
    {
        yield 'value parameter' => [
            '<f:variable name="foo" value="bar" />{foo}',
            'bar',
        ];
        yield 'tag content as value' => [
            '<f:variable name="foo">bar</f:variable>{foo}',
            'bar',
        ];
        yield 'variable inside loop used outside' => [
            '<f:for each="{0: \'foo\', 1: \'bar\'}" as="item"><f:variable name="lastItem" value="{item}" /></f:for>{lastItem}',
            'bar',
        ];
        yield 'variable assignment of zero' => [
            '<f:variable name="zero1" value="0" />{f:variable(name: \'zero2\', value: 0)}{zero1}{zero2}',
            '00',
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
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
