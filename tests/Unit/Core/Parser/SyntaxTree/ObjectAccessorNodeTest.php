<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for ObjectAccessorNode
 */
class ObjectAccessorNodeTest extends UnitTestCase
{

    /**
     * @test
     * @dataProvider getEvaluateTestValues
     * @param array $variables
     * @param string $path
     * @param mixed $expected
     */
    public function testEvaluateGetsExpectedValue(array $variables, $path, $expected)
    {
        $node = new ObjectAccessorNode($path);
        $renderingContext = $this->getMock(RenderingContextInterface::class);
        $variableContainer = new StandardVariableProvider($variables);
        $renderingContext->expects($this->any())->method('getVariableProvider')->will($this->returnValue($variableContainer));
        $value = $node->evaluate($renderingContext);
        $this->assertEquals($expected, $value);
    }

    /**
     * @return array
     */
    public function getEvaluateTestValues()
    {
        return [
            [['foo' => 'bar'], 'foo.notaproperty', null],
            [['foo' => 'bar'], '_all', ['foo' => 'bar']],
            [['foo' => 'bar'], 'foo', 'bar'],
            [['foo' => ['bar' => 'test']], 'foo.bar', 'test'],
            [['foo' => ['bar' => 'test'], 'dynamic' => 'bar'], 'foo.{dynamic}', 'test'],
            [['foo' => ['bar' => 'test'], 'dynamic' => ['sub' => 'bar']], 'foo.{dynamic.sub}', 'test'],
            [['foo' => ['bar' => 'test'], 'dynamic' => ['sub' => 'bar'], 'baz' => 'sub'], 'foo.{dynamic.{baz}}', 'test'],
        ];
    }

    /**
     * @test
     */
    public function testEvaluatedUsesVariableProviderGetByPath()
    {
        $node = new ObjectAccessorNode('foo.bar');
        $renderingContext = $this->getMock(RenderingContextInterface::class);
        $variableContainer = $this->getMock(StandardVariableProvider::class, []);
        $variableContainer->expects($this->once())->method('getByPath')->with('foo.bar', [])->will($this->returnValue('foo'));
        $renderingContext->expects($this->any())->method('getVariableProvider')->will($this->returnValue($variableContainer));
        $value = $node->evaluate($renderingContext);
        $this->assertEquals('foo', $value);
    }
}
