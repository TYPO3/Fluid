<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class InlineViewHelperTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function renderThrowsExceptionIfInlineFluidCodeIsInvalid(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1238169398);
        $view = new TemplateView();
        $view->assignMultiple(['code' => '<f:if condition="{undefinedVariable}">']);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:inline code="{code}" />');
        $view->render();
    }

    public static function renderDataProvider(): \Generator
    {
        yield 'empty string' => [
            '<f:inline code="{code}" />',
            [
                'code' => ''
            ],
            null,
        ];
        yield 'empty children closure' => [
            '<f:inline></f:inline>',
            [],
            null,
        ];
        yield 'undefined variable as children closure' => [
            '<f:inline>{undefined}</f:inline>',
            [],
            null,
        ];
        yield 'variable with null as value as children closure' => [
            '<f:inline>{iAmNull}</f:inline>',
            [
                'iAmNull' => null,
            ],
            null,
        ];
        yield 'valid code with undefined variable' => [
            '<f:inline code="{code}" />',
            [
                'code' => '{f:if(condition: undefinedVariable, then: \'foo\', else: \'bar\')}'
            ],
            'bar',
        ];
        yield 'valid code with defined variable' => [
            '<f:inline code="{code}" />',
            [
                'code' => '{f:if(condition: definedVariable, then: \'foo\', else: \'bar\')}',
                'definedVariable' => true
            ],
            'foo',
        ];
        yield 'tag content' => [
            '<f:inline>{code}</f:inline>',
            [
                'code' => '{f:if(condition: definedVariable, then: \'foo\', else: \'bar\')}',
                'definedVariable' => true
            ],
            'foo',
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
