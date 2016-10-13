<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\CastingExpressionNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToArray;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Class CastingExpressionNodeTest
 */
class CastingExpressionNodeTest extends UnitTestCase
{

    /**
     * @test
     */
    public function testEvaluateDelegatesToEvaluteExpression()
    {
        $subject = $this->getMock(
            CastingExpressionNode::class,
            ['dummy'],
            ['{test as string}', ['test as string']]
        );
        $view = new TemplateView();
        $context = new RenderingContext($view);
        $context->setVariableProvider(new StandardVariableProvider(['test' => 10]));
        $result = $subject->evaluate($context);
        $this->assertSame('10', $result);
    }

    /**
     * @test
     */
    public function testEvaluateInvalidExpressionThrowsException()
    {
        $view = new TemplateView();
        $renderingContext = new RenderingContext($view);
        $renderingContext->setVariableProvider(new StandardVariableProvider());
        $this->setExpectedException(ExpressionException::class);
        $result = CastingExpressionNode::evaluateExpression($renderingContext, 'suchaninvalidexpression as 1', []);
    }

    /**
     * @dataProvider getEvaluateExpressionTestValues
     * @param string $expression
     * @param array $variables
     * @param mixed $expected
     */
    public function testEvaluateExpression($expression, array $variables, $expected)
    {
        $view = new TemplateView();
        $renderingContext = new RenderingContext($view);
        $renderingContext->setVariableProvider(new StandardVariableProvider($variables));
        $result = CastingExpressionNode::evaluateExpression($renderingContext, $expression, []);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getEvaluateExpressionTestValues()
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
            ['mydate as DateTime', ['mydate' => 90000], \DateTime::createFromFormat('U', 90000)],
            ['mydate as DateTime', ['mydate' => 'January'], new \DateTime('January')],
            ['1 as namestoredinvariables', ['namestoredinvariables' => 'boolean'], true],
            ['mystring as array', ['mystring' => 'foo,bar'], ['foo', 'bar']],
            ['mystring as array', ['mystring' => 'foo , bar'], ['foo', 'bar']],
            ['myiterator as array', ['myiterator' => $arrayIterator], ['foo', 'bar']],
            ['myarray as array', ['myarray' => ['foo', 'bar']], ['foo', 'bar']],
            ['myboolean as array', ['myboolean' => true], []],
            ['myboolean as array', ['myboolean' => false], []],
            ['myobject as array', ['myobject' => $toArrayObject], ['name' => 'foobar']],
        ];
    }
}
