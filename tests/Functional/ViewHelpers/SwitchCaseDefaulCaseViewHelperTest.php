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

class SwitchCaseDefaulCaseViewHelperTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function renderThrowsExceptionIfCaseIsOutsideOfSwitch()
    {
        $this->expectException(Exception::class);
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:case value="foo">bar</f:case>');
        $view->render();
    }

    /**
     * @test
     */
    public function renderThrowsExceptionIfDefaultCaseIsOutsideOfSwitch()
    {
        $this->expectException(Exception::class);
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:defaultCase>bar</f:defaultCase>');
        $view->render();
    }

    public function renderDataProvider(): \Generator
    {
        yield 'without cases' => [
            '<f:switch expression="{value}">bar</f:switch>',
            ['value' => 'foo'],
            null,
        ];
        yield 'with matching case' => [
            '<f:switch expression="{value}">' .
                '<f:case value="option1">bar</f:case>' .
                '<f:case value="option2">baz</f:case>' .
                // @TODO if this line is part of the test case, the next case fails
                // (empty string vs null), maybe a Fluid caching issue?
                // '<f:defaultCase>default</f:defaultCase>' .
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
            null,
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
