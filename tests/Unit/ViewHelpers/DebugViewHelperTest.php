<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithoutToString;

/**
 * Testcase for DebugViewHelper
 */
class DebugViewHelperTest extends ViewHelperBaseTestCase
{
    /**
     * @return array
     */
    public function getStandardTestValues(): array
    {
        $arrayObject = new \ArrayObject(['foo' => 'bar']);
        $arrayIterator = new \ArrayIterator(['foo' => 'bar']);
        $recursive = clone $arrayObject;
        $recursive['recursive'] = $arrayObject;
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, 'Hello world');

        $variableProvider = new StandardVariableProvider(
            [
                'arrayObject' => $arrayObject,
                'arrayIterator' => $arrayIterator,
                'recursive' => $recursive,
                'stream' => $stream,
                'userWithName' => new UserWithoutToString('username'),
                'dateTime' => \DateTime::createFromFormat('U', '1468328915'),
            ]
        );
        $context = new RenderingContextFixture();
        $context->setVariableProvider($variableProvider);

        return [
            [
                "string 'test'" . PHP_EOL,
                $context,
                ['typeOnly' => false, 'html' => false, 'levels' => 1],
                [new TextNode('test')],
            ],
            [
                'string',
                $context,
                ['typeOnly' => true, 'html' => false, 'levels' => 1],
                [new TextNode('test')],
            ],
            [
                '<code>string = \'&lt;strong&gt;bold&lt;/strong&gt;\'</code>',
                $context,
                ['typeOnly' => false, 'html' => true, 'levels' => 1],
                [new TextNode('<strong>bold</strong>')]
            ],
            [
                '<code>array</code><ul><li>nested: <code>string = \'test&lt;strong&gt;bold&lt;/strong&gt;\'</code></li></ul>',
                $context,
                ['typeOnly' => false, 'html' => true, 'levels' => 1],
                [new ArrayNode(['nested' => 'test<strong>bold</strong>'])]
            ],
            [
                '<code>array</code><ul><li>foo: <code>string = \'bar\'</code></li></ul>',
                $context,
                ['typeOnly' => false, 'html' => true, 'levels' => 2],
                [new ArrayNode(['foo' => 'bar'])],
            ],
            [
                '<code>ArrayObject</code><ul><li>foo: <code>string = \'bar\'</code></li></ul>',
                $context,
                ['typeOnly' => false, 'html' => true, 'levels' => 2],
                [new ObjectAccessorNode('arrayObject')],
            ],
            [
                '<code>ArrayIterator</code><ul><li>foo: <code>string = \'bar\'</code></li></ul>',
                $context,
                ['typeOnly' => false, 'html' => true, 'levels' => 2],
                [new ObjectAccessorNode('arrayIterator')],
            ],
            [
                'array: ' . PHP_EOL . '  "foo": string \'bar\'' . PHP_EOL,
                $context,
                ['typeOnly' => false, 'html' => false, 'levels' => 3],
                [new ArrayNode(['foo' => 'bar'])],
            ],
            [
                'ArrayObject: ' . PHP_EOL . '  "foo": string \'bar\'' . PHP_EOL,
                $context,
                ['typeOnly' => false, 'html' => false, 'levels' => 3],
                [new ObjectAccessorNode('arrayObject')],
            ],
            [
                'ArrayIterator: ' . PHP_EOL . '  "foo": string \'bar\'' . PHP_EOL,
                $context,
                ['typeOnly' => false, 'html' => false, 'levels' => 3],
                [new ObjectAccessorNode('arrayIterator')],
            ],
            [
                UserWithoutToString::class . ': ' . PHP_EOL . '  "name": string \'username\'' . PHP_EOL,
                $context,
                ['typeOnly' => false, 'html' => false, 'levels' => 3],
                [new ObjectAccessorNode('userWithName')],
            ],
            [
                'null' . PHP_EOL,
                $context,
                ['typeOnly' => false, 'html' => false, 'levels' => 3],
            ],
            [
                'ArrayObject: ' . PHP_EOL . '  "foo": string \'bar\'' . PHP_EOL . '  "recursive": ArrayObject: *Recursion limited*',
                $context,
                ['typeOnly' => false, 'html' => false, 'levels' => 1],
                [new ObjectAccessorNode('recursive')],
            ],
            [
                '<code>ArrayObject</code><ul><li>foo: <code>string = \'bar\'</code></li><li>recursive: <code>ArrayObject</code><i>Recursion limited</i></li></ul>',
                $context,
                ['typeOnly' => false, 'html' => true, 'levels' => 1],
                [new ObjectAccessorNode('recursive')],
            ],
            [
                $this->anything(),
                $context,
                ['typeOnly' => false, 'html' => false, 'levels' => 1],
                [new ObjectAccessorNode('stream')],
            ],
            [
                'DateTime: ' . PHP_EOL . '  "class": string \'DateTime\'' . PHP_EOL .
                '  "ISO8601": string \'2016-07-12T13:08:35+00:00\'' . PHP_EOL . '  "UNIXTIME": integer 1468328915' . PHP_EOL,
                $context,
                ['typeOnly' => false, 'html' => false, 'levels' => 3],
                [new ObjectAccessorNode('dateTime')],
            ]
        ];
    }
}
