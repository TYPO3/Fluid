<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Parsing;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class CommaToleranceTest extends AbstractFunctionalTestCase
{
    public static function commaToleranceDataProvider(): array
    {
        return [
            ['<f:variable name="result" value="{abc: 1, def: 2,}" />', ['abc' => 1, 'def' => 2]],
            ['<f:variable name="result" value="{abc: 1, def: 2 ,}" />', ['abc' => 1, 'def' => 2]],
            ['<f:variable name="result" value="{abc: 1, def: 2, }" />', ['abc' => 1, 'def' => 2]],
            ['<f:variable name="result" value="{abc: 1, def: 2,,}" />', '{abc: 1, def: 2,,}'],
            ['<f:variable name="result" value="{abc: 1,, def: 2}" />', '{abc: 1,, def: 2}'],
            ['<f:variable name="result" value="{,abc: 1, def: 2}" />', '{,abc: 1, def: 2}'],
            ['<f:variable name="result" value="{f:if(condition: 1, then: 1, else: 0,)}" />', 1],
            ['<f:variable name="result" value="{f:if(condition: 1, then: 1, else: 0 ,)}" />', 1],
            ['<f:variable name="result" value="{f:if(condition: 1, then: 1, else: 0, )}" />', 1],
            ['<f:variable name="result" value="{f:if(condition: 1, then: 1, else: 0,,)}" />', '{f:if(condition: 1, then: 1, else: 0,,)}'],
            ['<f:variable name="result" value="{f:if(condition: 1,, then: 1, else: 0)}" />', '{f:if(condition: 1,, then: 1, else: 0)}'],
            ['<f:variable name="result" value="{f:if(,condition: 1, then: 1, else: 0)}" />', '{f:if(,condition: 1, then: 1, else: 0)}'],
        ];
    }

    #[DataProvider('commaToleranceDataProvider')]
    #[Test]
    public function commaTolerance(string $source, $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->render();
        self::assertSame($view->getRenderingContext()->getVariableProvider()->get('result'), $expected);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->render();
        self::assertSame($view->getRenderingContext()->getVariableProvider()->get('result'), $expected);
    }
}
