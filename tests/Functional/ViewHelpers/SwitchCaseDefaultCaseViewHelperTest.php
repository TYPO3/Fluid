<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class SwitchCaseDefaultCaseViewHelperTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function renderThrowsExceptionIfCaseIsOutsideOfSwitch(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1368112037);
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:case value="foo">bar</f:case>');
        $view->render();
    }

    /**
     * @test
     */
    public function renderThrowsExceptionIfDefaultCaseIsOutsideOfSwitch(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1368112037);
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:defaultCase>bar</f:defaultCase>');
        $view->render();
    }

    public static function renderDataProvider(): \Generator
    {
        yield 'without cases' => [
            '<f:switch expression="{value}">bar</f:switch>',
            ['value' => 'foo'],
            '',
        ];
        yield 'with matching case' => [
            '<f:switch expression="{value}">' .
                '<f:case value="option1">bar</f:case>' .
                '<f:case value="option2">baz</f:case>' .
                '<f:defaultCase>default</f:defaultCase>' .
            '</f:switch>',
            ['value' => 'option1'],
            'bar',
        ];
        yield 'without matching case' => [
            '<f:switch expression="{value}">' .
                '<f:case value="option1">bar</f:case>' .
                '<f:case value="option2">baz</f:case>' .
            '</f:switch>',
            ['value' => 'anotherValue'],
            '',
        ];
        yield 'with matching default case' => [
            '<f:switch expression="{value}">' .
                '<f:case value="option1">bar</f:case>' .
                '<f:defaultCase>default</f:defaultCase>' .
            '</f:switch>',
            ['value' => 'anotherValue'],
            'default',
        ];
        yield 'comparing different types' => [
            '<f:switch expression="{value}">' .
                '<f:case value="1">bar</f:case>' .
            '</f:switch>',
            ['value' => '1'],
            'bar',
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
    public function render(string $template, array $variables, $expected): void
    {
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());
    }
}
