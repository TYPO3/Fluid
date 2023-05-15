<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class SectionViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): \Generator
    {
        yield 'section will not render itself' => [
            '<f:section name="foo">bar</f:section>',
            '',
        ];
        yield 'render section without arguments before section was defined' => [
            '<f:render section="foo" /><f:section name="foo">bar</f:section>',
            'bar',
        ];
        yield 'render section without arguments after section was defined' => [
            '<f:section name="foo">bar</f:section><f:render section="foo" />',
            'bar',
        ];
        yield 'render section with arguments' => [
            '<f:section name="foo">{var}</f:section><f:render section="foo" arguments="{var: \'bar\'}" />',
            'bar',
        ];
        yield 'render section with arguments multiple times' => [
            '<f:section name="foo">{var}</f:section>' .
            '<f:render section="foo" arguments="{var: \'value1\'}" /> ' .
            '<f:render section="foo" arguments="{var: \'value2\'}" />',
            'value1 value2',
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
