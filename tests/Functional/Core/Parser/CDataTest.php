<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Parsing;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class CDataTest extends AbstractFunctionalTestCase
{
    public static function handleCdataInTemplateDataProvider(): array
    {
        return [
            'simple string' => [
                'some content <![CDATA[some content within cdata]]> more content',
                [],
                'some content  more content',
            ],

            'normal variable syntax' => [
                'some content <![CDATA[some {content} within cdata]]> more content',
                ['content' => 'foo'],
                'some content  more content',
            ],
            'normal ViewHelper tag syntax' => [
                'some content <![CDATA[<f:format.trim>  some content within cdata  </f:format.trim>]]> more content',
                [],
                'some content  more content',
            ],
            'normal ViewHelper inline syntax' => [
                'some content <![CDATA[{f:format.trim(value: \'  some content within cdata  \')}]]> more content',
                [],
                'some content  more content',
            ],
            'normal math syntax' => [
                'some content <![CDATA[{1 + 2}]]> more content',
                [],
                'some content  more content',
            ],
            'normal cast syntax' => [
                'some content <![CDATA[{var as float}]]> more content',
                ['var' => 1.0],
                'some content  more content',
            ],
            'normal ternary syntax' => [
                'some content <![CDATA[{var1 ? var2 : var3}]]> more content',
                ['var1' => 'foo', 'var2' => 'bar', 'var3' => 'baz'],
                'some content  more content',
            ],
        ];
    }

    #[Test]
    #[DataProvider('handleCdataInTemplateDataProvider')]
    #[IgnoreDeprecations]
    public function handleCdataInTemplate(string $template, array $variables, string $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->assignMultiple($variables);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        $output = $view->render();
        self::assertSame($expected, $output);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->assignMultiple($variables);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        $output = $view->render();
        self::assertSame($expected, $output);
    }
}
