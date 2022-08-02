<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class ForViewHelperTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function renderThrowsExceptionIfSubjectIsNotTraversable()
    {
        $this->expectException(\InvalidArgumentException::class);
        $value = new \stdClass();
        $view = new TemplateView();
        $view->assignMultiple(['value' => $value]);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:for each="{value}" as="item">{item}</f:for>');
        $view->render();
    }

    /**
     * @test
     */
    public function renderThrowsExceptionIfSubjectIsInvalid()
    {
        $this->expectException(Exception::class);
        $value = new \DateTime();
        $view = new TemplateView();
        $view->assignMultiple(['value' => $value]);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:for each="{value}" as="item">{item}</f:for>');
        $view->render();
    }

    public function renderDataProvider(): \Generator
    {
        $value = new \ArrayObject();
        yield 'empty for empty object' => [
            '<f:for each="{value}" as="item">{item}</f:for>',
            ['value' => $value],
            '',
        ];
        $value = [];
        yield 'empty for empty array' => [
            '<f:for each="{value}" as="item">{item}</f:for>',
            ['value' => $value],
            '',
        ];
        $value = [0, 1, 2, 3];
        yield 'items are displayed' => [
            '<f:for each="{value}" as="item">{item} </f:for>',
            ['value' => $value],
            '0 1 2 3 ',
        ];
        $value = [0, 1, 2, 3];
        yield 'reverse is respected for array' => [
            '<f:for each="{value}" as="item" reverse="true">{item} </f:for>',
            ['value' => $value],
            '3 2 1 0 ',
        ];
        $value = new \ArrayIterator([0, 1, 2, 3]);
        yield 'reverse is respected for object' => [
            '<f:for each="{value}" as="item" reverse="true">{item} </f:for>',
            ['value' => $value],
            '3 2 1 0 ',
        ];
        $value = new \ArrayObject(['key1' => 'value1', 'key2' => 'value2']);
        yield 'object gets traversed' => [
            '<f:for each="{value}" as="item">{item} </f:for>',
            ['value' => $value],
            'value1 value2 ',
        ];
        yield 'iterator contains information' => [
            '<ul>' .
                '<f:for each="{0:1, 1:2, 2:3, 3:4}" as="item" iteration="iterator">' .
                    '<li>Index: {iterator.index} Cycle: {iterator.cycle} Total: {iterator.total}{f:if(condition: iterator.isEven, then: \' Even\')}{f:if(condition: iterator.isOdd, then: \' Odd\')}{f:if(condition: iterator.isFirst, then: \' First\')}{f:if(condition: iterator.isLast, then: \' Last\')}</li>' .
                '</f:for>' .
            '</ul>',
            [],
            '<ul>' .
                '<li>Index: 0 Cycle: 1 Total: 4 Odd First</li>' .
                '<li>Index: 1 Cycle: 2 Total: 4 Even</li>' .
                '<li>Index: 2 Cycle: 3 Total: 4 Odd</li>' .
                '<li>Index: 3 Cycle: 4 Total: 4 Even Last</li>' .
            '</ul>',
        ];
        $value = ['item'];
        yield 'iterator not available if not requested' => [
            '<f:for each="{value}" as="item">Total: {iterator.total}</f:for>',
            ['value' => $value],
            'Total: ',
        ];
        $value = ['item' => 'bar', 'baz' => 2];
        yield 'key attribute is respected' => [
            '<f:for each="{value}" key="key" as="item">{key}: {item}, </f:for>',
            ['value' => $value],
            'item: bar, baz: 2, ',
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
    public function render(string $template, array $variables, string $expected): void
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
