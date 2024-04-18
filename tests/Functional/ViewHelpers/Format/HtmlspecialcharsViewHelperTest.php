<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithoutToString;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithToString;
use TYPO3Fluid\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper;

final class HtmlspecialcharsViewHelperTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function renderDoesNotModifySourceIfItIsAnObjectThatCantBeConvertedToAString(): void
    {
        $user = new UserWithoutToString('Xaver <b>Cross-Site</b>');
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->assign('user', $user);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:format.htmlspecialchars>{user}</f:format.htmlspecialchars>');
        self::assertSame($user, $view->render());
    }

    /**
     * @test
     */
    public function viewHelperDeactivatesEscapingInterceptor(): void
    {
        self::assertFalse((new HtmlspecialcharsViewHelper())->isOutputEscapingEnabled());
    }

    public static function renderDataProvider(): \Generator
    {
        yield 'value as argument' => [
            '<f:format.htmlspecialchars value="Some string" keepQuotes="false" encoding="UTF-8" doubleEncode="false"/>',
            [],
            'Some string',
        ];
        yield 'value as tag content' => [
            '<f:format.htmlspecialchars>Some string</f:format.htmlspecialchars>',
            [],
            'Some string',
        ];
        yield '[argument] render does not modify string without special characters' => [
            '<f:format.htmlspecialchars value="This is a sample text without special characters." />',
            [],
            'This is a sample text without special characters.',
        ];
        yield '[argument] render decodes simple string' => [
            '<f:format.htmlspecialchars value="Some special characters: &©\"\'" />',
            [],
            'Some special characters: &amp;©&quot;&#039;',
        ];
        yield '[argument] render respects "keepQuotes" argument' => [
            '<f:format.htmlspecialchars keepQuotes="true" value="Some special characters: &©\"" />',
            [],
            'Some special characters: &amp;©"',
        ];

        yield '[argument] render respects "encoding" argument' => [
            '<f:format.htmlspecialchars encoding="ISO-8859-1" value="{value}" />',
            ['value' => mb_convert_encoding('Some special characters: &"\'', 'ISO-8859-1', 'UTF-8')],
            'Some special characters: &amp;&quot;&#039;',
        ];
        yield '[argument] render converts already converted entities by default' => [
            '<f:format.htmlspecialchars value="already &quot;encoded&quot;" />',
            [],
            'already &amp;quot;encoded&amp;quot;',
        ];
        yield '[argument] render does not convert already converted entities if "doubleEncode" is FALSE' => [
            '<f:format.htmlspecialchars doubleEncode="false" value="already &quot;encoded&quot;" />',
            [],
            'already &quot;encoded&quot;',
        ];
        yield '[argument] render returns unmodified source if it is a float' => [
            '<f:format.htmlspecialchars value="123.45" />',
            [],
            123.45,
        ];
        yield '[argument] render returns unmodified source if it is an integer' => [
            '<f:format.htmlspecialchars value="12345" />',
            [],
            12345,
        ];
        yield '[argument] render returns unmodified source if it is a boolean' => [
            '<f:format.htmlspecialchars value="true" />',
            [],
            'true',
        ];

        yield '[tag content] render does not modify string without special characters' => [
            '<f:format.htmlspecialchars>This is a sample text without special characters.</f:format.htmlspecialchars>',
            [],
            'This is a sample text without special characters.',
        ];
        yield '[tag content] render decodes simple string' => [
            '<f:format.htmlspecialchars>Some special characters: &©"\'</f:format.htmlspecialchars>',
            [],
            'Some special characters: &amp;©&quot;&#039;',
        ];
        yield '[tag content] render respects "keepQuotes" argument' => [
            '<f:format.htmlspecialchars keepQuotes="true">Some special characters: &©"</f:format.htmlspecialchars>',
            [],
            'Some special characters: &amp;©"',
        ];

        yield '[tag content] render respects "encoding" argument' => [
            '<f:format.htmlspecialchars encoding="ISO-8859-1">{value}</f:format.htmlspecialchars>',
            ['value' => mb_convert_encoding('Some special characters: &"\'', 'ISO-8859-1', 'UTF-8')],
            'Some special characters: &amp;&quot;&#039;',
        ];
        yield '[tag content] render converts already converted entities by default' => [
            '<f:format.htmlspecialchars>already &quot;encoded&quot;</f:format.htmlspecialchars>',
            [],
            'already &amp;quot;encoded&amp;quot;',
        ];
        yield '[tag content] render does not convert already converted entities if "doubleEncode" is FALSE' => [
            '<f:format.htmlspecialchars doubleEncode="false">already &quot;encoded&quot;</f:format.htmlspecialchars>',
            [],
            'already &quot;encoded&quot;',
        ];
        yield '[tag content] render returns unmodified source if it is a float' => [
            '<f:format.htmlspecialchars>123.45</f:format.htmlspecialchars>',
            [],
            '123.45',
        ];
        yield '[tag content] render returns unmodified source if it is an integer' => [
            '<f:format.htmlspecialchars>12345</f:format.htmlspecialchars>',
            [],
            '12345',
        ];
        yield '[tag content] render returns unmodified source if it is a boolean' => [
            '<f:format.htmlspecialchars>true</f:format.htmlspecialchars>',
            [],
            'true',
        ];

        $user = new UserWithToString('Xaver <b>Cross-Site</b>');
        yield 'object is converted to string' => [
            '<f:format.htmlspecialchars>{user}</f:format.htmlspecialchars>',
            ['user' => $user],
            'Xaver &lt;b&gt;Cross-Site&lt;/b&gt;',
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
