<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Conditions;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class BasicConditionsTest extends AbstractFunctionalTestCase
{
    public function basicConditionDataProvider(): array
    {
        return [
            ['1 == 1', true],
            ['1 != 2', true],
            ['1 == 2', false],
            ['1 === 1', true],
            // expected value based on php versions behaviour
            ['\'foo\' == 0', PHP_VERSION_ID < 80000],
            // expected value based on php versions behaviour
            ['1.1 >= \'foo\'', PHP_VERSION_ID < 80000],
            ['\'String containing word \"false\" in text\'', true],
            ['\'  FALSE  \'', true],
            // expected value based on php versions behaviour
            ['\'foo\' > 0', !(PHP_VERSION_ID < 80000)],
            ['FALSE', false],
            ['(FALSE || (FALSE || 1)', true],
            ['(FALSE or (FALSE or 1)', true],

            // integers
            ['13 == \'13\'', true],
            ['13 === \'13\'', false],

            // floats
            ['13.37 == \'13.37\'', true],
            ['13.37 === \'13.37\'', false],

            // groups
            ['(1 && (\'foo\' == \'foo\') && (TRUE || 1)) && 0 != 1', true],
            ['(1 && (\'foo\' == \'foo\') && (TRUE || 1)) && 0 != 1 && FALSE', false]
        ];
    }

    /**
     * @test
     * @dataProvider basicConditionDataProvider
     */
    public function basicCondition(string $source, bool $expected): void
    {
        $source = '<f:if condition="' . $source . '" then="yes" else="no" />';
        $expected = $expected === true ? 'yes' : 'no';

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render());
    }
}
