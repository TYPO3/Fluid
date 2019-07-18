<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree\Expression;

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
    public function testEvaluateInvalidExpressionThrowsException(): void
    {
        $view = new TemplateView();
        $renderingContext = new RenderingContext($view);
        $renderingContext->setVariableProvider(new StandardVariableProvider());
        $this->setExpectedException(ExpressionException::class);
        (new CastingExpressionNode(['suchaninvalidexpression', 'as', '1']))->evaluate($renderingContext);
    }

    /**
     * @dataProvider getEvaluateExpressionTestValues
     * @param string $expression
     * @param array $variables
     * @param mixed $expected
     */
    public function testEvaluateExpression(string $expression, array $variables, $expected): void
    {
        $parts = explode(' ', $expression);
        $view = new TemplateView();
        $renderingContext = new RenderingContext($view);
        $renderingContext->setVariableProvider(new StandardVariableProvider($variables));
        $result = (new CastingExpressionNode($parts))->evaluate($renderingContext);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getEvaluateExpressionTestValues(): array
    {
        $arrayIterator = new \ArrayIterator(['foo', 'bar']);
        $toArrayObject = new UserWithToArray('foobar');
        $dateTime = new \DateTime('now');
        return [
            ['mystring as float', ['mystring' => '1.23'], 1.23],
            ['myvariable as integer', ['myvariable' => 321], 321],
            ['myinteger as string', ['myinteger' => 111], '111'],
            ['myinteger as boolean', ['myinteger' => 1], true],
            ['mydate as DateTime', ['mydate' => 90000], \DateTime::createFromFormat('U', '90000')],
            ['mydate as DateTime', ['mydate' => 'January'], new \DateTime('January')],
            ['mystring as array', ['mystring' => 'foo,bar'], ['foo', 'bar']],
            ['mystring as array', ['mystring' => 'foo , bar'], ['foo', 'bar']],
            ['myiterator as array', ['myiterator' => $arrayIterator], ['foo', 'bar']],
            ['myarray as array', ['myarray' => ['foo', 'bar']], ['foo', 'bar']],
            ['myboolean as array', ['myboolean' => true], []],
            ['myboolean as array', ['myboolean' => false], []],
            ['myobject as array', ['myobject' => $toArrayObject], ['name' => 'foobar']],
            ['mydatetime as array', ['mydatetime' => $dateTime], [$dateTime]],
        ];
    }
}
