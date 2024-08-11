<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class SwitchCaseDefaultCaseViewHelperTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function renderThrowsExceptionIfCaseIsOutsideOfSwitch(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1368112037);
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:case value="foo">bar</f:case>');
        $view->render();
    }

    #[Test]
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

    #[DataProvider('renderDataProvider')]
    #[Test]
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

    public static function ignoreTextAndWhitespacesDataProvider(): array
    {
        return [
            'Ignores whitespace inside parent switch outside case children' => [
                '<f:switch expression="1">   <f:case value="2">NO</f:case>   <f:case value="1">YES</f:case>   </f:switch>',
                '   ',
            ],
            'Ignores text inside parent switch outside case children' => [
                '<f:switch expression="1">TEXT<f:case value="2">NO</f:case><f:case value="1">YES</f:case></f:switch>',
                'TEXT',
            ],
            'Ignores text and whitespace inside parent switch outside case children 1' => [
                '<f:switch expression="1">   TEXT   <f:case value="2">NO</f:case><f:case value="1">YES</f:case></f:switch>',
                'TEXT',
            ],
            'Ignores text and whitespace inside parent switch outside case children 2' => [
                '<f:switch expression="1">   TEXT   <f:case value="2">NO</f:case><f:case value="1">YES</f:case></f:switch>',
                '   ',
            ],
        ];
    }

    #[DataProvider('ignoreTextAndWhitespacesDataProvider')]
    #[Test]
    public function ignoreTextAndWhitespaces(string $source, string $notExpected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        self::assertStringNotContainsString($notExpected, $output);

        // Second run to test cached template parsing
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        self::assertStringNotContainsString($notExpected, $output);
    }
}
