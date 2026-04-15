<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class PushViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): iterable
    {
        $simpleArray = ['a', 'b', 'c', 'd'];
        $arrayWithKeys = [
            'keyA' => 'a',
            'keyB' => 'b',
            'keyC' => 'c',
            'keyD' => 'd',
        ];

        yield 'simple array' => [
            'arguments' => ['inputArray' => $simpleArray],
            'src' => '<f:variable name="resultArray"></f:variable>'
                . '<f:for each="{inputArray}" as="value">'
                . '<f:push name="resultArray" value="{value}" />'
                . '</f:for>',
            'expectation' => ['a', 'b', 'c', 'd'],
        ];

        yield 'simple array (inline)' => [
            'arguments' => ['inputArray' => $simpleArray],
            'src' => '{f:variable(name: "resultArray")}'
                . '{item -> f:push(name: "resultArray") -> f:for(each: "{inputArray}", as: "item")}',
            'expectation' => ['a', 'b', 'c', 'd'],
        ];

        yield 'array with keys' => [
            'arguments' => ['inputArray' => $arrayWithKeys],
            'src' => '<f:variable name="resultArray"></f:variable>'
                . '<f:for each="{inputArray}" as="value" key="key">'
                . '<f:push name="resultArray" value="{value}" key="{key}"/>'
                . '</f:for>',
            'expectation' => [
                'keyA' => 'a',
                'keyB' => 'b',
                'keyC' => 'c',
                'keyD' => 'd',
            ],
        ];

        yield 'variable name not defined' => [
            'arguments' => ['inputArray' => $arrayWithKeys],
            'src' => '<f:for each="{inputArray}" as="value" key="key">'
                . '<f:push name="resultArray" value="{value}" key="{key}"/>'
                . '</f:for>',
            'expectation' => [
                'keyA' => 'a',
                'keyB' => 'b',
                'keyC' => 'c',
                'keyD' => 'd',
            ],
        ];
    }

    #[Test]
    #[DataProvider('renderDataProvider')]
    public function render(array $arguments, string $src, array $expectation): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        $view->render();
        self::assertSame($expectation, $view->getRenderingContext()->getVariableProvider()->get('resultArray'));

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        $view->render();
        self::assertSame($expectation, $view->getRenderingContext()->getVariableProvider()->get('resultArray'));
    }
}
