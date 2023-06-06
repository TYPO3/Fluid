<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree\Expression;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\CastingExpressionNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithToArray;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

final class CastingExpressionNodeTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testEvaluateDelegatesToEvaluteExpression(): void
    {
        $subject = new CastingExpressionNode('{test as string}', ['test as string']);
        $context = new RenderingContext();
        $context->setVariableProvider(new StandardVariableProvider(['test' => 10]));
        $result = $subject->evaluate($context);
        self::assertSame('10', $result);
    }

    /**
     * @test
     */
    public function testEvaluateInvalidExpressionThrowsException(): void
    {
        $this->expectException(ExpressionException::class);
        $renderingContext = new RenderingContext();
        $renderingContext->setVariableProvider(new StandardVariableProvider());
        CastingExpressionNode::evaluateExpression($renderingContext, 'suchaninvalidexpression as 1', []);
    }

    public static function getEvaluateExpressionTestValues(): array
    {
        $arrayIterator = new \ArrayIterator(['foo', 'bar']);
        $toArrayObject = new UserWithToArray('foobar');
        return [
            ['123 as string', [], '123'],
            ['1 as boolean', [], true],
            ['0 as boolean', [], false],
            ['0 as array', [], [0]],
            ['1 as array', [], [1]],
            ['mystring as float', ['mystring' => '1.23'], 1.23],
            ['myvariable as integer', ['myvariable' => 321], 321],
            ['myinteger as string', ['myinteger' => 111], '111'],
            ['mydate as DateTime', ['mydate' => 90000], \DateTime::createFromFormat('U', '90000')],
            ['mydate as DateTime', ['mydate' => 'January'], new \DateTime('January')],
            ['1 as namestoredinvariables', ['namestoredinvariables' => 'boolean'], true],
            ['mystring as array', ['mystring' => 'foo,bar'], ['foo', 'bar']],
            ['mystring as array', ['mystring' => 'foo , bar'], ['foo', 'bar']],
            ['myiterator as array', ['myiterator' => $arrayIterator], ['foo', 'bar']],
            ['myarray as array', ['myarray' => ['foo', 'bar']], ['foo', 'bar']],
            ['myboolean as array', ['myboolean' => true], []],
            ['myboolean as array', ['myboolean' => false], []],
            ['myobject as array', ['myobject' => $toArrayObject], ['name' => 'foobar']],
            ['myjsonstring as array', ['myjsonstring' => '["foo", "bar"]'], [0 => 'foo', 1 => 'bar']],
            ['myjsonstring as array', ['myjsonstring' => '{"foo":"bar","favs":{"color":"red"}}'], ['foo' => 'bar', 'favs' => ['color' => 'red']]],
            ['myjsonstring as array', ['myjsonstring' => ' { "json with"  :  "spaces " }' ], ['json with' => 'spaces ']],
            ['myarray as json', ['myarray' => [0 => 'foo', 1 => 'bar']], '["foo","bar"]'],
            ['myarray as json', ['myarray' => ['foo' => 'bar', 'favs' => ['color' => 'red']]], '{"foo":"bar","favs":{"color":"red"}}'],
        ];
    }

    /**
     * @dataProvider getEvaluateExpressionTestValues
     * @param mixed $expected
     * @test
     */
    public function testEvaluateExpression(string $expression, array $variables, $expected): void
    {
        $renderingContext = new RenderingContext();
        $renderingContext->setVariableProvider(new StandardVariableProvider($variables));
        $result = CastingExpressionNode::evaluateExpression($renderingContext, $expression, []);
        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function testEvaluateExpressionThrowsExceptionIfJsonSyntaxError(): void
    {
        $this->expectException(\JsonException::class);
        $subject = new CastingExpressionNode('{test as array}', ['test as array']);
        $context = new RenderingContext();
        $context->setVariableProvider(new StandardVariableProvider(['test' => '{"invalid json"::123}']));
        $result = $subject->evaluate($context);
    }
}
