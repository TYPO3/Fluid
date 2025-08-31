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
    #[Test]
    public function invalidArgumentTypeUncached(): void
    {
        $source = '<f:count subject="test" />';

        self::expectExceptionCode(1256475113);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->render();
    }

    #[Test]
    public function invalidArgumentTypeCached(): void
    {
        $source = '<f:count subject="test" />';

        self::expectExceptionCode(1256475113);

        try {
            $view = new TemplateView();
            $view->getRenderingContext()->setCache(self::$cache);
            $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
            $view->render();
        } catch (\Exception) {
        }

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->render();
    }

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
            ['booleanArg', null, false], // behaves differently because of BooleanParser
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
            ['boolArg', null, false], // behaves differently because of BooleanParser
            ['boolArg', new stdClass(), true],

            ['stringArg', true, '1'],
            ['stringArg', false, ''],
            ['stringArg', 2, '2'],
            ['stringArg', 1, '1'],
            ['stringArg', 0, '0'],
            ['stringArg', -1, '-1'],
            ['stringArg', 1.5, '1.5'],
            ['stringArg', '', ''],
            ['stringArg', 'test', 'test'],
            ['stringArg', null, null],

            ['integerArg', true, 1],
            ['integerArg', false, 0],
            ['integerArg', 2, 2],
            ['integerArg', 1, 1],
            ['integerArg', 0, 0],
            ['integerArg', -1, -1],
            ['integerArg', 1.5, 1],
            ['integerArg', '', 0],
            ['integerArg', 'test', 0],
            ['integerArg', null, null],

            ['intArg', true, 1],
            ['intArg', false, 0],
            ['intArg', 2, 2],
            ['intArg', 1, 1],
            ['intArg', 0, 0],
            ['intArg', -1, -1],
            ['intArg', 1.5, 1],
            ['intArg', '', 0],
            ['intArg', 'test', 0],
            ['intArg', null, null],

            ['floatArg', true, 1.0],
            ['floatArg', false, 0.0],
            ['floatArg', 2, 2.0],
            ['floatArg', 1, 1.0],
            ['floatArg', 0, 0.0],
            ['floatArg', -1, -1.0],
            ['floatArg', 1.5, 1.5],
            ['floatArg', '', 0.0],
            ['floatArg', 'test', 0.0],
            ['floatArg', null, null],

            ['doubleArg', true, 1.0],
            ['doubleArg', false, 0.0],
            ['doubleArg', 2, 2.0],
            ['doubleArg', 1, 1.0],
            ['doubleArg', 0, 0.0],
            ['doubleArg', -1, -1.0],
            ['doubleArg', 1.5, 1.5],
            ['doubleArg', '', 0.0],
            ['doubleArg', 'test', 0.0],
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

    public static function unionTypesDataProvider(): array
    {
        return [
            ['foo', 'string'],
            [['foo'], 'array'],
        ];
    }

    #[DataProvider('unionTypesDataProvider')]
    #[Test]
    public function unionTypes(mixed $argumentValue, string $expectedResult): void
    {
        $variables = ['argumentValue' => $argumentValue];
        $source = '<test:unionType arg="{argumentValue}" />';

        $view = new TemplateView();
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expectedResult, $view->render(), 'uncached');

        $view = new TemplateView();
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expectedResult, $view->render(), 'cached');
    }

    public static function invalidUnionTypeThrowsExceptionDataProvider(): array
    {
        return [
            [123, 1256475113],
            [new \DateTime(), 1256475113],
        ];
    }

    #[DataProvider('invalidUnionTypeThrowsExceptionDataProvider')]
    #[Test]
    public function invalidUnionTypeThrowsException(mixed $argumentValue, int $expectedExceptionCode): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionCode($expectedExceptionCode);

        $variables = ['argumentValue' => $argumentValue];
        $source = '<test:unionType arg="{argumentValue}" />';

        $view = new TemplateView();
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->render();
    }
}
