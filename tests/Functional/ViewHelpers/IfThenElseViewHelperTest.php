<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class IfThenElseViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): \Generator
    {
        yield 'then argument, else argument, no verdict, prefers else' => [
            '<f:if then="thenArgument" else="elseArgument" />',
            [],
            'elseArgument',
        ];
        yield 'then body, else body, no verdict, prefers else' => [
            '<f:if>' .
                '<f:then>thenBody</f:then>' .
                '<f:else>elseBody</f:else>' .
            '</f:if>',
            [],
            'elseBody',
        ];
        yield 'then argument, verdict true' => [
            '<f:if condition="{verdict}" then="thenArgument" />',
            ['verdict' => true],
            'thenArgument',
        ];
        yield 'then argument, verdict false' => [
            '<f:if condition="{verdict}" then="thenArgument" />',
            ['verdict' => false],
            '',
        ];
        yield 'then argument, else argument, verdict true' => [
            '<f:if condition="{verdict}" then="thenArgument" else="elseArgument" />',
            ['verdict' => true],
            'thenArgument',
        ];
        yield 'then argument, else argument, verdict false' => [
            '<f:if condition="{verdict}" then="thenArgument" else="elseArgument" />',
            ['verdict' => false],
            'elseArgument',
        ];
        yield 'else argument, verdict true' => [
            '<f:if condition="{verdict}" else="elseArgument" />',
            ['verdict' => true],
            null,
        ];
        yield 'else argument, verdict false' => [
            '<f:if condition="{verdict}" else="elseArgument" />',
            ['verdict' => false],
            'elseArgument',
        ];
        yield 'then body, verdict true' => [
            '<f:if condition="{verdict}">' .
                'thenBody' .
            '</f:if>',
            ['verdict' => true],
            'thenBody',
        ];
        yield 'then body, verdict false' => [
            '<f:if condition="{verdict}">' .
                'thenBody' .
            '</f:if>',
            ['verdict' => false],
            '',
        ];
        yield 'then body, then child, verdict true, prefers child' => [
            '<f:if condition="{verdict}">' .
                'thenBody' .
                '<f:then>thenChild</f:then>' .
            '</f:if>',
            ['verdict' => true],
            'thenChild',
        ];
        yield 'then body, then child, verdict false' => [
            '<f:if condition="{verdict}">' .
                'thenBody' .
                '<f:then>thenChild</f:then>' .
            '</f:if>',
            ['verdict' => false],
            '',
        ];
        yield 'then body, else child, verdict true, ignores then body' => [
            '<f:if condition="{verdict}">' .
                'thenBody' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => true],
            '',
        ];
        yield 'then body, else child, verdict false' => [
            '<f:if condition="{verdict}">' .
                'thenBody' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => false],
            'elseChild',
        ];
        yield 'then child1, then child2, verdict true' => [
            '<f:if condition="{verdict}">' .
                '<f:then>thenChild1</f:then>' .
                '<f:then>thenChild2</f:then>' .
            '</f:if>',
            ['verdict' => true],
            'thenChild1',
        ];
        yield 'then child1, then child2, verdict false' => [
            '<f:if condition="{verdict}">' .
                '<f:then>thenChild1</f:then>' .
                '<f:then>thenChild2</f:then>' .
            '</f:if>',
            ['verdict' => false],
            '',
        ];
        yield 'else child1, else child2, verdict true' => [
            '<f:if condition="{verdict}">' .
                '<f:else>elseChild1</f:else>' .
                '<f:else>elseChild2</f:else>' .
            '</f:if>',
            ['verdict' => true],
            '',
        ];
        yield 'else child1, else child2, verdict false' => [
            '<f:if condition="{verdict}">' .
                '<f:else>elseChild1</f:else>' .
                '<f:else>elseChild2</f:else>' .
            '</f:if>',
            ['verdict' => false],
            'elseChild1',
        ];
        yield 'then child, else child, verdict true' => [
            '<f:if condition="{verdict}">' .
                '<f:then>thenChild</f:then>' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => true],
            'thenChild',
        ];
        yield 'then child, else child, verdict false' => [
            '<f:if condition="{verdict}">' .
                '<f:then>thenChild</f:then>' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => false],
            'elseChild',
        ];
        yield 'then argument, then child, verdict true, prefers then argument' => [
            '<f:if condition="{verdict}" then="thenArgument">' .
                '<f:then>thenChild</f:then>' .
            '</f:if>',
            ['verdict' => true],
            'thenArgument',
        ];
        yield 'then argument, then child, verdict false' => [
            '<f:if condition="{verdict}" then="thenArgument">' .
                '<f:then>thenChild</f:then>' .
            '</f:if>',
            ['verdict' => false],
            '',
        ];
        yield 'then argument, then child, else argument, else child, verdict false, prefers else argument' => [
            '<f:if condition="{verdict}" then="thenArgument" else="elseArgument">' .
                '<f:then>thenChild</f:then>' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => false],
            'elseArgument',
        ];

        yield 'then child, else if child, if verdict true, elseif verdict true' => [
            '<f:if condition="{verdict}">' .
                '<f:then>thenChild</f:then>' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
            '</f:if>',
            ['verdict' => true, 'verdictElseIf' => true],
            'thenChild',
        ];
        yield 'then child, else if child, if verdict false, elseif verdict true' => [
            '<f:if condition="{verdict}">' .
                '<f:then>thenChild</f:then>' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf' => true],
            'elseIfChild',
        ];
        yield 'then child, else if child, if verdict false, elseif verdict false' => [
            '<f:if condition="{verdict}">' .
                '<f:then>thenChild</f:then>' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf' => false],
            '',
        ];

        yield 'then child, else if child, else child, if verdict true, elseif verdict true' => [
            '<f:if condition="{verdict}">' .
                '<f:then>thenChild</f:then>' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => true, 'verdictElseIf' => true],
            'thenChild',
        ];
        yield 'then child, else if child, else child, if verdict false, elseif verdict true' => [
            '<f:if condition="{verdict}">' .
                '<f:then>thenChild</f:then>' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf' => true],
            'elseIfChild',
        ];
        yield 'then child, else if child, else child, if verdict false, elseif verdict false' => [
            '<f:if condition="{verdict}">' .
                '<f:then>thenChild</f:then>' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf' => false],
            'elseChild',
        ];

        yield 'then child, else if child1, else if child 2, else child, if verdict true, elseif1 verdict true, elseif2 verdict true' => [
            '<f:if condition="{verdict}">' .
                '<f:then>thenChild</f:then>' .
                '<f:else if="{verdictElseIf1}">elseIfChild1</f:else>' .
                '<f:else if="{verdictElseIf2}">elseIfChild2</f:else>' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => true, 'verdictElseIf1' => true, 'verdictElseIf2' => true],
            'thenChild',
        ];
        yield 'then child, else if child1, else if child 2, else child, if verdict false, elseif1 verdict true, elseif2 verdict true' => [
            '<f:if condition="{verdict}">' .
                '<f:then>thenChild</f:then>' .
                '<f:else if="{verdictElseIf1}">elseIfChild1</f:else>' .
                '<f:else if="{verdictElseIf2}">elseIfChild2</f:else>' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf1' => true, 'verdictElseIf2' => true],
            'elseIfChild1',
        ];
        yield 'then child, else if child1, else if child 2, else child, if verdict false, elseif1 verdict false, elseif2 verdict true' => [
            '<f:if condition="{verdict}">' .
                '<f:then>thenChild</f:then>' .
                '<f:else if="{verdictElseIf1}">elseIfChild1</f:else>' .
                '<f:else if="{verdictElseIf2}">elseIfChild2</f:else>' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf1' => false, 'verdictElseIf2' => true],
            'elseIfChild2',
        ];
        yield 'then child, else if child1, else if child 2, else child, if verdict false, elseif1 verdict false, elseif2 verdict false' => [
            '<f:if condition="{verdict}">' .
                '<f:then>thenChild</f:then>' .
                '<f:else if="{verdictElseIf1}">elseIfChild1</f:else>' .
                '<f:else if="{verdictElseIf2}">elseIfChild2</f:else>' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf1' => false, 'verdictElseIf2' => false],
            'elseChild',
        ];

        yield 'then argument, else if child, else child, if verdict true, elseif verdict true' => [
            '<f:if condition="{verdict}" then="thenArgument">' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => true, 'verdictElseIf' => true],
            'thenArgument',
        ];
        yield 'then argument, else if child, else child, if verdict false, elseif verdict true' => [
            '<f:if condition="{verdict}" then="thenArgument">' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf' => true],
            'elseIfChild',
        ];
        yield 'then argument, else if child, else child, if verdict false, elseif verdict false' => [
            '<f:if condition="{verdict}" then="thenArgument">' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
                '<f:else>elseChild</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf' => false],
            'elseChild',
        ];

        yield 'then child, else if child, else argument, if verdict true, elseif verdict true' => [
            '<f:if condition="{verdict}" else="elseArgument">' .
                '<f:then>thenChild</f:then>' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
            '</f:if>',
            ['verdict' => true, 'verdictElseIf' => true],
            'thenChild',
        ];
        yield 'then child, else if child, else argument, if verdict false, elseif verdict true' => [
            '<f:if condition="{verdict}" else="elseArgument">' .
                '<f:then>thenChild</f:then>' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf' => true],
            'elseIfChild',
        ];
        yield 'then child, else if child, else argument, if verdict false, elseif verdict false' => [
            '<f:if condition="{verdict}" else="elseArgument">' .
                '<f:then>thenChild</f:then>' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf' => false],
            'elseArgument',
        ];

        yield 'else if child, if verdict true, elseif verdict true' => [
            '<f:if condition="{verdict}">' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
            '</f:if>',
            ['verdict' => true, 'verdictElseIf' => true],
            '',
        ];
        yield 'else if child, if verdict false, elseif verdict true' => [
            '<f:if condition="{verdict}">' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf' => true],
            'elseIfChild',
        ];
        yield 'else if child, if verdict false, elseif verdict false' => [
            '<f:if condition="{verdict}">' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf' => false],
            '',
        ];

        /*
         * @todo: broken non-compiled, ok compiled
        yield 'else argument, else if child, if verdict false, elseif verdict true' => [
            '<f:if condition="{verdict}" else="elseArgument">' .
                '<f:else if="{verdictElseIf}">elseIfChild</f:else>' .
            '</f:if>',
            ['verdict' => false, 'verdictElseIf' => true],
            'elseIfChild',
        ];
        */
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
