<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\ViewHelper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ViewHelperArgumentTypesTest extends AbstractFunctionalTestCase
{
    public static function scalarArgumentsDataProvider(): array
    {
        return [
            ['booleanArg', true, true],
            ['booleanArg', false, false],
            ['booleanArg', 2, true],
            ['booleanArg', 1, true],
            ['booleanArg', 0, false],
            ['booleanArg', -1, true],
            ['booleanArg', 1.5, true],
            ['booleanArg', '', false],
            ['booleanArg', 'test', true],
            ['booleanArg', null, false], // @todo this should be null
            ['booleanArg', new stdClass(), true],

            ['boolArg', true, true],
            ['boolArg', false, false],
            ['boolArg', 2, true],
            ['boolArg', 1, true],
            ['boolArg', 0, false],
            ['boolArg', -1, true],
            ['boolArg', 1.5, true],
            ['boolArg', '', false],
            ['boolArg', 'test', true],
            ['boolArg', null, false], // @todo this should be null
            ['boolArg', new stdClass(), true],

            ['stringArg', true, true], // @todo this should be '1'
            ['stringArg', false, false], // @todo this should be ''
            ['stringArg', 2, 2], // @todo this should be '2'
            ['stringArg', 1, 1], // @todo this should be '1'
            ['stringArg', 0, 0], // @todo this should be '0'
            ['stringArg', -1, -1], // @todo this should be '-1'
            ['stringArg', 1.5, 1.5], // @todo this should be '1.5'
            ['stringArg', '', ''],
            ['stringArg', 'test', 'test'],
            ['stringArg', null, null],

            ['integerArg', true, true], // @todo this should be 1
            ['integerArg', false, false], // @todo this should be 0
            ['integerArg', 2, 2],
            ['integerArg', 1, 1],
            ['integerArg', 0, 0],
            ['integerArg', -1, -1],
            ['integerArg', 1.5, 1.5], // @todo this should be 1
            ['integerArg', '', ''], // @todo this should be 0
            ['integerArg', 'test', 'test'], // @todo this should be 0
            ['integerArg', null, null],

            ['intArg', true, true], // @todo this should be 1
            ['intArg', false, false], // @todo this should be 0
            ['intArg', 2, 2],
            ['intArg', 1, 1],
            ['intArg', 0, 0],
            ['intArg', -1, -1],
            ['intArg', 1.5, 1.5], // @todo this should be 1
            ['intArg', '', ''], // @todo this should be 0
            ['intArg', 'test', 'test'], // @todo this should be 0
            ['intArg', null, null],

            ['floatArg', true, true], // @todo this should be 1.0
            ['floatArg', false, false], // @todo this should be 0.0
            ['floatArg', 2, 2], // @todo this should be 2.0
            ['floatArg', 1, 1], // @todo this should be 1.0
            ['floatArg', 0, 0], // @todo this should be 0.0
            ['floatArg', -1, -1], // @todo this should be -1.0
            ['floatArg', 1.5, 1.5],
            ['floatArg', '', ''], // @todo this should be 0.0
            ['floatArg', 'test', 'test'], // @todo this should be 0.0
            ['floatArg', null, null],

            ['doubleArg', true, true], // @todo this should be 1.0
            ['doubleArg', false, false], // @todo this should be 0.0
            ['doubleArg', 2, 2], // @todo this should be 2.0
            ['doubleArg', 1, 1], // @todo this should be 1.0
            ['doubleArg', 0, 0], // @todo this should be 0.0
            ['doubleArg', -1, -1], // @todo this should be -1.0
            ['doubleArg', 1.5, 1.5],
            ['doubleArg', '', ''], // @todo this should be 0.0
            ['doubleArg', 'test', 'test'], // @todo this should be 0.0
            ['doubleArg', null, null],
        ];
    }

    #[DataProvider('scalarArgumentsDataProvider')]
    #[Test]
    public function scalarArguments(string $argumentName, mixed $argumentValue, mixed $expectedValue): void
    {
        $variables = ['argumentValue' => $argumentValue];
        $source = '<test:scalarArguments ' . $argumentName . '="{argumentValue}" />';

        $view = new TemplateView();
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $result = unserialize($view->render());
        self::assertSame($expectedValue, $result[$argumentName], 'uncached');

        $view = new TemplateView();
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $result = unserialize($view->render());
        self::assertSame($expectedValue, $result[$argumentName], 'cached');
    }
}
