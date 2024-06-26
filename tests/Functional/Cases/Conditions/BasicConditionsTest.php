<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Conditions;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class BasicConditionsTest extends AbstractFunctionalTestCase
{
    public static function basicConditionDataProvider(): array
    {
        return [
            ['1 == 1', true],
            ['1 != 2', true],
            ['1 == 2', false],
            ['1 === 1', true],
            ['\'foo\' == 0', false],
            ['1.1 >= \'foo\'', false],
            ['\'String containing word \"false\" in text\'', true],
            ['\'  FALSE  \'', true],
            ['\'foo\' > 0', true],
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
            ['(1 && (\'foo\' == \'foo\') && (TRUE || 1)) && 0 != 1 && FALSE', false],
        ];
    }

    #[DataProvider('basicConditionDataProvider')]
    #[Test]
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
