<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Parsing;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ArraySyntaxTest extends AbstractFunctionalTestCase
{
    public static function arraySyntaxDataProvider(): array
    {
        return [
            // Edge case: Fluid treats this expression as an object accessor instead of an array
            'single array spread without whitespace' => [
                '<f:variable name="result" value="{...input1}" />',
                [
                    'input1' => ['abc' => 1, 'def' => 2],
                ],
                null,
            ],
            // Edge case: Fluid treats this expression as an object accessor instead of an array
            'single array spread with whitespace after' => [
                '<f:variable name="result" value="{...input1 }" />',
                [
                    'input1' => ['abc' => 1, 'def' => 2],
                ],
                null,
            ],
            'single array spread with whitespace before' => [
                '<f:variable name="result" value="{ ...input1}" />',
                [
                    'input1' => ['abc' => 1, 'def' => 2],
                ],
                ['abc' => 1, 'def' => 2],
            ],
            'single array spread' => [
                '<f:variable name="result" value="{ ...input1 }" />',
                [
                    'input1' => ['abc' => 1, 'def' => 2],
                ],
                ['abc' => 1, 'def' => 2],
            ],
            'multiple array spreads' => [
                '<f:variable name="result" value="{ ...input1, ...input2 }" />',
                [
                    'input1' => ['abc' => 1, 'def' => 2],
                    'input2' => ['ghi' => 3],
                ],
                ['abc' => 1, 'def' => 2, 'ghi' => 3],
            ],
            'multiple array spreads mixed with other items' => [
                '<f:variable name="result" value="{ first: 1, ...input1, middle: \'middle value\', ...input2, last: { sub: 1 } }" />',
                [
                    'input1' => ['abc' => 1, 'def' => 2],
                    'input2' => ['ghi' => 3],
                ],
                ['first' => 1, 'abc' => 1, 'def' => 2, 'middle' => 'middle value', 'ghi' => 3, 'last' => ['sub' => 1]],
            ],
            'overwrite static value' => [
                '<f:variable name="result" value="{ abc: 10, ...input1 }" />',
                [
                    'input1' => ['abc' => 1, 'def' => 2],
                ],
                ['abc' => 1, 'def' => 2],
            ],
            'overwrite spreaded value' => [
                '<f:variable name="result" value="{ ...input1, abc: 10 }" />',
                [
                    'input1' => ['abc' => 1, 'def' => 2],
                ],
                ['abc' => 10, 'def' => 2],
            ],
            'overwrite spreaded value with spreaded value' => [
                '<f:variable name="result" value="{ ...input1, ...input2 }" />',
                [
                    'input1' => ['abc' => 1, 'def' => 2],
                    'input2' => ['abc' => 10],
                ],
                ['abc' => 10, 'def' => 2],
            ],
            'whitespace variants' => [
                '<f:variable name="result" value="{... input1 , ... input2}" />',
                [
                    'input1' => ['abc' => 1, 'def' => 2],
                    'input2' => ['ghi' => 3],
                ],
                ['abc' => 1, 'def' => 2, 'ghi' => 3],
            ]
        ];
    }

    /**
     * @test
     * @dataProvider arraySyntaxDataProvider
     */
    public function arraySyntax(string $source, array $variables, $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->assignMultiple($variables);
        $view->render();
        self::assertSame($view->getRenderingContext()->getVariableProvider()->get('result'), $expected);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->assignMultiple($variables);
        $view->render();
        self::assertSame($view->getRenderingContext()->getVariableProvider()->get('result'), $expected);
    }
}
