<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\ViewHelper\InvalidArgumentValueException;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ForViewHelperTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function renderThrowsExceptionIfSubjectIsNotTraversable(): void
    {
        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionCode(1256475113);
        $view = new TemplateView();
        $view->assignMultiple(['value' => new \stdClass()]);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:for each="{value}" as="item">{item}</f:for>');
        $view->render();
    }

    #[Test]
    public function renderThrowsExceptionIfSubjectIsInvalid(): void
    {
        $this->expectException(InvalidArgumentValueException::class);
        $this->expectExceptionCode(1256475113);
        $view = new TemplateView();
        $view->assignMultiple(['value' => new \stdClass()]);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:for each="{value}" as="item">{item}</f:for>');
        $view->render();
    }

    public static function renderDataProvider(): \Generator
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

        $value = ['key1' => 'value1', 'key2' => 'value2'];
        yield 'reverse is respected for associative array' => [
            '<f:for each="{value}" as="item" reverse="true">{item} </f:for>',
            ['value' => $value],
            'value2 value1 ',
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
            '<ul>'
                . '<f:for each="{0:1, 1:2, 2:3, 3:4}" as="item" iteration="iterator">'
                    . '<li>Index: {iterator.index} Cycle: {iterator.cycle} Total: {iterator.total}{f:if(condition: iterator.isEven, then: \' Even\')}{f:if(condition: iterator.isOdd, then: \' Odd\')}{f:if(condition: iterator.isFirst, then: \' First\')}{f:if(condition: iterator.isLast, then: \' Last\')}</li>'
                . '</f:for>'
            . '</ul>',
            [],
            '<ul>'
                . '<li>Index: 0 Cycle: 1 Total: 4 Odd First</li>'
                . '<li>Index: 1 Cycle: 2 Total: 4 Even</li>'
                . '<li>Index: 2 Cycle: 3 Total: 4 Odd</li>'
                . '<li>Index: 3 Cycle: 4 Total: 4 Even Last</li>'
            . '</ul>',
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

        $value = ['bar', 2];
        yield 'key contains numerical index' => [
            '<f:for each="{value}" key="key" as="item">{key}: {item}, </f:for>',
            ['value' => $value],
            '0: bar, 1: 2, ',
        ];

        $value = new \ArrayIterator(['key1' => 'value1', 'key2' => 'value2']);
        yield 'keys are preserved with objects implementing iterator interface' => [
            '<f:for each="{value}" key="key" as="item">{key}: {item}, </f:for>',
            ['value' => $value],
            'key1: value1, key2: value2, ',
        ];

        $value = new \SplObjectStorage();
        $object1 = new \stdClass();
        $value->offsetSet($object1);
        $object2 = new \stdClass();
        $value->offsetSet($object2, 'foo');
        $object3 = new \stdClass();
        $value->offsetSet($object3, 'bar');
        yield 'keys are a numerical index with objects of type SplObjectStorage' => [
            '<f:for each="{value}" key="key" as="item">{key}</f:for>',
            ['value' => $value],
            '012',
        ];

        $value = ['foo' => 'fooValue', 'Fluid' => 'FluidStandalone', 'TYPO3' => 'rocks'];
        yield 'iterator works' => [
            '<f:for each="{value}" key="key" as="item" iteration="myIterator">'
                . 'key: {key}, item: {item}, '
                . 'index: {myIterator.index}, cycle: {myIterator.cycle}, total: {myIterator.total}, '
                . 'isFirst: {myIterator.isFirst}, isLast: {myIterator.isLast}, '
                . 'isEven: {myIterator.isEven}, isOdd: {myIterator.isOdd}' . chr(10)
            . '</f:for>',
            ['value' => $value],
            'key: foo, item: fooValue, index: 0, cycle: 1, total: 3, isFirst: 1, isLast: , isEven: , isOdd: 1' . chr(10)
            . 'key: Fluid, item: FluidStandalone, index: 1, cycle: 2, total: 3, isFirst: , isLast: , isEven: 1, isOdd: ' . chr(10)
            . 'key: TYPO3, item: rocks, index: 2, cycle: 3, total: 3, isFirst: , isLast: 1, isEven: , isOdd: 1' . chr(10),
        ];

        $value = ['bar', 2];
        yield 'variables are restored after loop' => [
            '{key} {item} <f:for each="{value}" key="key" as="item">{key}: {item}, </f:for> {key} {item}',
            ['value' => $value, 'key' => '[key before]', 'item' => '[item before]'],
            '[key before] [item before] 0: bar, 1: 2,  [key before] [item before]',
        ];

        $value = ['bar', 2];
        yield 'variables are restored after loop if overwritten in loop' => [
            '<f:for each="{value}" as="item"><f:variable name="item" value="overwritten" /></f:for>{item}',
            ['value' => $value],
            'overwritten',
        ];

        $value = ['bar', 2];
        yield 'local variables can be converted to global variables inside loop' => [
            '<f:for each="{value}" as="item">{item}|<f:variable name="item" value="overwritten" />{item}|</f:for>{item}',
            ['value' => $value],
            'bar|overwritten|2|overwritten|overwritten',
        ];

        $value = ['bar', 2];
        yield 'variables set inside loop can be used after loop' => [
            '<f:for each="{value}" key="key" as="item"><f:variable name="foo" value="bar" /></f:for>{foo}',
            ['value' => $value],
            'bar',
        ];

        $value = ['bar', 2];
        yield 'existing variables can be modified in loop and retain the value set in the loop' => [
            '<f:for each="{value}" key="key" as="item"><f:variable name="foo" value="bar" /></f:for>{foo}',
            ['value' => $value, 'foo' => 'fallback'],
            'bar',
        ];
    }

    #[DataProvider('renderDataProvider')]
    #[Test]
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

    public static function renderWithRepeatedIteratorKeysDataProvider(): \Generator
    {
        $numericKeys = static function (): \Generator {
            yield from [2, 1];
            yield from [4, 3];
        };
        $stringKeys = static function (): \Generator {
            yield from ['a' => 2, 'b' => 1];
            yield from ['a' => 4, 'b' => 3];
        };
        $composed = static function (): \Generator {
            yield from (static function (): \Generator {
                yield 'a';
                yield 'k' => 'b';
            })();
            yield from ['c', 'd'];
            yield 'k' => 'e';
        };
        yield 'reverse with repeated numeric keys' => [
            'valueFactory' => $numericKeys,
            'src' => '<f:for each="{value}" as="item" key="key" reverse="true">{key}:{item},</f:for>',
            'expectation' => '1:3,0:4,1:1,0:2,',
        ];
        yield 'reverse with repeated string keys' => [
            'valueFactory' => $stringKeys,
            'src' => '<f:for each="{value}" as="item" key="key" reverse="true">{key}:{item},</f:for>',
            'expectation' => 'b:3,a:4,b:1,a:2,',
        ];
        yield 'iteration with repeated numeric keys' => [
            'valueFactory' => $numericKeys,
            'src' => '<f:for each="{value}" as="item" key="key" iteration="i">{i.cycle}/{i.total} {key}:{item},</f:for>',
            'expectation' => '1/4 0:2,2/4 1:1,3/4 0:4,4/4 1:3,',
        ];
        yield 'reverse and iteration with composed generators' => [
            'valueFactory' => $composed,
            'src' => '<f:for each="{value}" as="item" key="key" reverse="true" iteration="i">{i.cycle}/{i.total} {key}:{item},</f:for>',
            'expectation' => '1/5 k:e,2/5 1:d,3/5 0:c,4/5 k:b,5/5 0:a,',
        ];
    }

    #[DataProvider('renderWithRepeatedIteratorKeysDataProvider')]
    #[Test]
    public function renderWithRepeatedIteratorKeys(\Closure $valueFactory, string $src, mixed $expectation): void
    {
        // Generators can't be rewound, so each rendering gets a fresh one. The variable name is unique
        // per data set, so the first rendering can't reuse a template compiled by another test.
        $variableName = 'value' . hash('xxh3', (string)$this->dataName());
        $src = str_replace('{value', '{' . $variableName, $src);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $templateIdentifier = $view->getRenderingContext()->getTemplatePaths()->getTemplateIdentifier();
        self::assertFalse($view->getRenderingContext()->getTemplateCompiler()->has($templateIdentifier), 'First rendering must be uncompiled');
        $view->assign($variableName, $valueFactory());
        self::assertSame($expectation, $view->render());

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        self::assertTrue($view->getRenderingContext()->getTemplateCompiler()->has($templateIdentifier), 'Second rendering must use the compiled template');
        $view->assign($variableName, $valueFactory());
        self::assertSame($expectation, $view->render());
    }
}
