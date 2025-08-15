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
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithoutToString;
use TYPO3Fluid\Fluid\View\TemplateView;

final class DebugViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): \Generator
    {
        yield 'not existing variable' => [
            '<f:debug>{value}</f:debug>',
            [],
            'null' . PHP_EOL,
        ];
        yield 'not existing variable html' => [
            '<f:debug html="1">{value}</f:debug>',
            [],
            '<code>NULL = NULL</code>',
        ];

        yield 'null' => [
            '<f:debug>{value}</f:debug>',
            ['value' => null],
            'null' . PHP_EOL,
        ];
        yield 'null html' => [
            '<f:debug html="1">{value}</f:debug>',
            ['value' => null],
            '<code>NULL = NULL</code>',
        ];

        yield 'string' => [
            '<f:debug>{value}</f:debug>',
            ['value' => 'test'],
            "string 'test'" . PHP_EOL,
        ];
        yield 'string html' => [
            '<f:debug html="1">{value}</f:debug>',
            ['value' => 'test'],
            "<code>string = 'test'</code>",
        ];

        yield 'html string' => [
            '<f:debug>{value}</f:debug>',
            ['value' => 'test<strong>bold</strong>'],
            "string 'test<strong>bold</strong>'" . PHP_EOL,
        ];
        yield 'html string html' => [
            '<f:debug html="1">{value}</f:debug>',
            ['value' => 'test<strong>bold</strong>'],
            "<code>string = 'test&lt;strong&gt;bold&lt;/strong&gt;'</code>",
        ];

        yield 'array nested html string' => [
            '<f:debug>{value}</f:debug>',
            [
                'value' => [
                    'nested' => 'test<strong>bold</strong>',
                ],
            ],
            'array: ' . PHP_EOL
            . '  "nested": string \'test<strong>bold</strong>\'' . PHP_EOL,
        ];
        yield 'array nested html string html' => [
            '<f:debug html="1">{value}</f:debug>',
            [
                'value' => [
                    'nested' => 'test<strong>bold</strong>',
                ],
            ],
            "<code>array</code><ul><li>nested: <code>string = 'test&lt;strong&gt;bold&lt;/strong&gt;'</code></li></ul>",
        ];

        yield 'type only' => [
            '<f:debug typeOnly="1">{value}</f:debug>',
            ['value' => 'test'],
            'string',
        ];
        yield 'type only html' => [
            '<f:debug typeOnly="1" html="1">{value}</f:debug>',
            ['value' => 'test'],
            'string',
        ];

        yield 'nested array' => [
            '<f:debug>{value}</f:debug>',
            [
                'value' => [
                    'nested' => 'test',
                ],
            ],
            'array: ' . PHP_EOL
            . '  "nested": string \'test\'' . PHP_EOL,
        ];
        yield 'nested array html' => [
            '<f:debug html="1">{value}</f:debug>',
            [
                'value' => [
                    'nested' => 'test',
                ],
            ],
            '<code>array</code><ul><li>nested: <code>string = \'test\'</code></li></ul>',
        ];

        yield 'array iterator object' => [
            '<f:debug>{value}</f:debug>',
            ['value' => new \ArrayIterator(['foo' => 'bar'])],
            'ArrayIterator: ' . PHP_EOL
            . '  "foo": string \'bar\'' . PHP_EOL,
        ];
        yield 'array iterator object html' => [
            '<f:debug html="1">{value}</f:debug>',
            ['value' => new \ArrayIterator(['foo' => 'bar'])],
            '<code>ArrayIterator</code><ul><li>foo: <code>string = \'bar\'</code></li></ul>',
        ];

        yield 'array object' => [
            '<f:debug>{value}</f:debug>',
            ['value' => new \ArrayObject(['foo' => 'bar'])],
            'ArrayObject: ' . PHP_EOL
            . '  "foo": string \'bar\'' . PHP_EOL,
        ];
        yield 'array object html' => [
            '<f:debug html="1">{value}</f:debug>',
            ['value' => new \ArrayObject(['foo' => 'bar'])],
            '<code>ArrayObject</code><ul><li>foo: <code>string = \'bar\'</code></li></ul>',
        ];

        yield 'casual object' => [
            '<f:debug>{value}</f:debug>',
            ['value' => new UserWithoutToString('username')],
            'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithoutToString: ' . PHP_EOL
            . '  "name": string \'username\'' . PHP_EOL,
        ];
        yield 'casual object html' => [
            '<f:debug html="1">{value}</f:debug>',
            ['value' => new UserWithoutToString('username')],
            '<code>TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithoutToString</code><ul><li>name: <code>string = \'username\'</code></li></ul>',
        ];

        yield 'datetime object' => [
            '<f:debug>{value}</f:debug>',
            ['value' => \DateTime::createFromFormat('U', '1468328915')],
            'DateTime: ' . PHP_EOL
            . '  "class": string \'DateTime\'' . PHP_EOL
            . '  "ISO8601": string \'2016-07-12T13:08:35+00:00\'' . PHP_EOL
            . '  "UNIXTIME": integer 1468328915' . PHP_EOL,
        ];
        yield 'datetime object html' => [
            '<f:debug html="1">{value}</f:debug>',
            ['value' => \DateTime::createFromFormat('U', '1468328915')],
            '<code>DateTime</code>'
            . '<ul>'
                . '<li>class: <code>string = \'DateTime\'</code></li>'
                . '<li>ISO8601: <code>string = \'2016-07-12T13:08:35+00:00\'</code></li>'
                . '<li>UNIXTIME: <code>integer = 1468328915</code></li>'
            . '</ul>',
        ];

        $value = fopen('php://memory', 'r+');
        fwrite($value, 'Hello world');
        yield 'stream' => [
            '<f:debug>{value}</f:debug>',
            ['value' => $value],
            'resource: ' . PHP_EOL
            . '  "timed_out": boolean false' . PHP_EOL
            . '  "blocked": boolean true' . PHP_EOL
            . '  "eof": boolean false' . PHP_EOL
            . '  "wrapper_type": string \'PHP\'' . PHP_EOL
            . '  "stream_type": string \'MEMORY\'' . PHP_EOL
            . '  "mode": string \'w+b\'' . PHP_EOL
            . '  "unread_bytes": integer 0' . PHP_EOL
            . '  "seekable": boolean true' . PHP_EOL
            . '  "uri": string \'php://memory\'' . PHP_EOL,
        ];
        yield 'stream html' => [
            '<f:debug html="1">{value}</f:debug>',
            ['value' => $value],
            '<code>resource</code>'
            . '<ul>'
                . '<li>timed_out: <code>boolean = false</code></li>'
                . '<li>blocked: <code>boolean = true</code></li>'
                . '<li>eof: <code>boolean = false</code></li>'
                . '<li>wrapper_type: <code>string = \'PHP\'</code></li>'
                . '<li>stream_type: <code>string = \'MEMORY\'</code></li>'
                . '<li>mode: <code>string = \'w+b\'</code></li>'
                . '<li>unread_bytes: <code>integer = 0</code></li>'
                . '<li>seekable: <code>boolean = true</code></li>'
                . '<li>uri: <code>string = \'php://memory\'</code></li>'
            . '</ul>',
        ];

        $arrayObject = new \ArrayObject(['foo' => 'bar']);
        $value = $arrayObject;
        $value['recursive'] = $arrayObject; /** @phpstan-ignore-line Fine for now to create a recursive object */
        yield 'recursive object' => [
            '<f:debug>{value}</f:debug>',
            ['value' => $value],
            'ArrayObject: ' . PHP_EOL
            . '  "foo": string \'bar\'' . PHP_EOL
            . '  "recursive": ArrayObject: ' . PHP_EOL
            . '    "foo": string \'bar\'' . PHP_EOL
            . '    "recursive": ArrayObject: ' . PHP_EOL
            . '      "foo": string \'bar\'' . PHP_EOL
            . '      "recursive": ArrayObject: ' . PHP_EOL
            . '        "foo": string \'bar\'' . PHP_EOL
            . '        "recursive": ArrayObject: ' . PHP_EOL
            . '          "foo": string \'bar\'' . PHP_EOL
            . '          "recursive": ArrayObject: *Recursion limited*',
        ];
        yield 'recursive object html' => [
            '<f:debug html="1">{value}</f:debug>',
            ['value' => $value],
            '<code>ArrayObject</code>'
            . '<ul>'
                . '<li>foo: <code>string = \'bar\'</code></li>'
                . '<li>recursive: <code>ArrayObject</code>'
                    . '<ul>'
                        . '<li>foo: <code>string = \'bar\'</code></li>'
                        . '<li>recursive: <code>ArrayObject</code>'
                            . '<ul>'
                                . '<li>foo: <code>string = \'bar\'</code></li>'
                                . '<li>recursive: <code>ArrayObject</code>'
                                    . '<ul>'
                                        . '<li>foo: <code>string = \'bar\'</code></li>'
                                        . '<li>recursive: <code>ArrayObject</code>'
                                            . '<ul>'
                                                . '<li>foo: <code>string = \'bar\'</code></li>'
                                                . '<li>recursive: <code>ArrayObject</code><i>Recursion limited</i></li>'
                                            . '</ul>'
                                        . '</li>'
                                    . '</ul>'
                                . '</li>'
                            . '</ul>'
                        . '</li>'
                    . '</ul>'
                . '</li>'
            . '</ul>',
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
}
