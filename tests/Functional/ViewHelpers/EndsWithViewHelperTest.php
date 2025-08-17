<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3\CMS\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class EndsWithViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): \Generator
    {
        yield 'empty search and subject, search true' => [
            '<f:endsWith search="{search}" subject="{subject}" then="thenArgument" />',
            ['search' => '', 'subject' => ''],
            'thenArgument',
        ];
        yield 'empty search string, search true' => [
            '<f:endsWith search="{search}" subject="{subject}" then="thenArgument" />',
            ['search' => '', 'subject' => 'subject'],
            'thenArgument',
        ];
        yield 'empty subject string, no else, search false' => [
            '<f:endsWith search="{search}" subject="{subject}" then="thenArgument" />',
            ['search' => 'search', 'subject' => ''],
            null,
        ];
        yield 'empty subject string, no then, search false' => [
            '<f:endsWith search="{search}" subject="{subject}" else="elseArgument" />',
            ['search' => 'search', 'subject' => ''],
            'elseArgument',
        ];
        yield 'search and subject given, search true' => [
            '<f:endsWith search="{search}" subject="{subject}" then="thenArgument" />',
            ['search' => 'search', 'subject' => 'search'],
            'thenArgument',
        ];
        yield 'search and subject given, search false' => [
            '<f:endsWith search="{search}" subject="{subject}" then="thenArgument" else="elseArgument" />',
            ['search' => 'search', 'subject' => 'subject'],
            'elseArgument',
        ];
        yield 'leading whitespace, empty result body, search false' => [
            ' <f:endsWith search="{search}" subject="{subject}"><f:variable name="foo" value="bar" /></f:endsWith>',
            ['search' => 'search', 'subject' => 'subject'],
            ' ',
        ];
        yield 'result body, search true' => [
            '<f:endsWith search="{search}" subject="{subject}">Foo</f:endsWith>',
            ['search' => 'search', 'subject' => 'search'],
            'Foo',
        ];
        yield 'then child, else child, search true' => [
            '<f:endsWith search="{search}" subject="{subject}">'
            . '<f:then>thenChild</f:then>'
            . '<f:else>elseChild</f:else>'
            . '</f:endsWith>',
            ['search' => 'search', 'subject' => 'search'],
            'thenChild',
        ];
        yield 'then child, else child, search false' => [
            '<f:endsWith search="{search}" subject="{subject}">'
            . '<f:then>thenChild</f:then>'
            . '<f:else>elseChild</f:else>'
            . '</f:endsWith>',
            ['search' => 'search', 'subject' => 'subject'],
            'elseChild',
        ];
        yield 'inline syntax, search true' => [
            '{f:endsWith(search: search, subject: subject)}',
            ['search' => 'search', 'subject' => 'search'],
            true,
        ];
        yield 'inline syntax, search false' => [
            '{f:endsWith(search: search, subject:  subject)}',
            ['search' => 'search', 'subject' => 'subject'],
            false,
        ];
        yield 'inline syntax, then argument, search true' => [
            '{f:endsWith(search: search, subject:  subject ,then:\'thenArgument\')}',
            ['search' => 'search', 'subject' => 'search'],
            'thenArgument',
        ];
        yield 'nested example, inside if with condition, search true' => [
            '<f:variable name="condition" value="{false}" />'
            . '<f:if condition="{condition} || {f:endsWith(search: search, subject: subject)}">'
            . 'It Works!'
            . '</f:if>',
            ['search' => 'search', 'subject' => 'search'],
            'It Works!',
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
}
