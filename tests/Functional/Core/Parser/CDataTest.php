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

final class CDataTest extends AbstractFunctionalTestCase
{
    public static function handleCdataInTemplateDataProvider(): array
    {
        return [
            'simple string' => [
                'some content <![CDATA[some content within cdata]]> more content',
                [],
                'some content some content within cdata more content',
            ],

            'normal variable syntax' => [
                'some content <![CDATA[some {content} within cdata]]> more content',
                ['content' => 'foo'],
                'some content some {content} within cdata more content',
            ],
            'normal ViewHelper tag syntax' => [
                'some content <![CDATA[<f:format.trim>  some content within cdata  </f:format.trim>]]> more content',
                [],
                'some content <f:format.trim>  some content within cdata  </f:format.trim> more content',
            ],
            'normal ViewHelper inline syntax' => [
                'some content <![CDATA[{f:format.trim(value: \'  some content within cdata  \')}]]> more content',
                [],
                'some content {f:format.trim(value: \'  some content within cdata  \')} more content',
            ],
            'normal math syntax' => [
                'some content <![CDATA[{1 + 2}]]> more content',
                [],
                'some content {1 + 2} more content',
            ],
            'normal cast syntax' => [
                'some content <![CDATA[{var as float}]]> more content',
                ['var' => 1.0],
                'some content {var as float} more content',
            ],
            'normal ternary syntax' => [
                'some content <![CDATA[{var1 ? var2 : var3}]]> more content',
                ['var1' => 'foo', 'var2' => 'bar', 'var3' => 'baz'],
                'some content {var1 ? var2 : var3} more content',
            ],

            'CDATA variable syntax' => [
                'some content <![CDATA[some {{{content}}} within cdata]]> more content',
                ['content' => 'foo'],
                'some content some foo within cdata more content',
            ],
            'CDATA ViewHelper inline syntax' => [
                'some content <![CDATA[{{{f:format.trim(value: \'  some content within cdata  \')}}}]]> more content',
                [],
                'some content some content within cdata more content',
            ],
            'CDATA ViewHelper inline syntax with simple variable use' => [
                'some content <![CDATA[{{{f:format.trim(value: content)}}}]]> more content',
                ['content' => 'foo'],
                'some content foo more content',
            ],
            'CDATA ViewHelper inline syntax with variable use' => [
                'some content <![CDATA[{{{f:format.trim(value: \'{content}\')}}}]]> more content',
                ['content' => 'foo'],
                'some content foo more content',
            ],
            'CDATA ViewHelper inline syntax with CDATA variable use' => [
                'some content <![CDATA[{{{f:format.trim(value: \'{{{content}}}\')}}}]]> more content',
                ['content' => 'foo'],
                'some content  more content',
            ],
            'CDATA math syntax' => [
                'some content <![CDATA[{{{1 + 2}}}]]> more content',
                [],
                'some content 3 more content',
            ],
            'CDATA cast syntax' => [
                'some content <![CDATA[{{{var as integer}}}]]> more content',
                ['var' => 1.1],
                'some content 1 more content',
            ],
            'CDATA ternary syntax' => [
                'some content <![CDATA[{{{var1 ? var2 : var3}}}]]> more content',
                ['var1' => 'foo', 'var2' => 'bar', 'var3' => 'baz'],
                'some content bar more content',
            ],

            'html attribute example' => [
                '
<![CDATA[
<div
    x-data="{
        test: null,
        init() {
            test = \'foo\';
        }
    }"
>
    {{{test}}}
</div>
]]>
                ',
                ['test' => 'bar'],
                '

<div
    x-data="{
        test: null,
        init() {
            test = \'foo\';
        }
    }"
>
    bar
</div>

                ',
            ],
            'inline css example' => [
                '
<style>
<![CDATA[
    @media (min-width: 1000px) {
        p {
            background-color: {{{color}}};
        }
    }
]]>
</style>
                ',
                ['color' => 'red'],
                '
<style>

    @media (min-width: 1000px) {
        p {
            background-color: red;
        }
    }

</style>
                ',
            ],
            'inline js example' => [
                '
<script>
<![CDATA[
    const settings = {
        countries: {{{countries -> f:format.json() -> f:format.raw()}}},
    };
]]>
</script>
                ',
                ['countries' => [['key' => 'de', 'name' => 'Germany', 'short' => 'DE'], ['key' => 'us', 'name' => 'United States of America', 'short' => 'US']]],
                '
<script>

    const settings = {
        countries: [{"key":"de","name":"Germany","short":"DE"},{"key":"us","name":"United States of America","short":"US"}],
    };

</script>
                ',
            ],
        ];
    }

    #[Test]
    #[DataProvider('handleCdataInTemplateDataProvider')]
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
