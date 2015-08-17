<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\NodeConverter;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\TernaryExpressionNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class NodeConverterTest
 */
class NodeConverterTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testSetVariableCounter() {
		$instance = new NodeConverter(new TemplateCompiler());
		$instance->setVariableCounter(10);
		$this->assertAttributeEquals(10, 'variableCounter', $instance);
	}

	/**
	 * @test
	 * @dataProvider getConvertTestValues
	 * @param NodeInterface $node
	 * @param string $expected
	 */
	public function testConvert(NodeInterface $node, $expected) {
		$instance = new NodeConverter(new TemplateCompiler());
		$method = new \ReflectionMethod($instance, 'convert');
		$method->setAccessible(TRUE);
		$result = $method->invokeArgs($instance, array($node));
		$this->assertEquals($expected, $result['execution']);
	}

	/**
	 * @return array
	 */
	public function getConvertTestValues() {
		$treeBooleanRoot = new RootNode();
		$treeBooleanRoot->addChildNode(new TextNode('1'));
		$treeBooleanRoot->addChildNode(new TextNode('!='));
		$treeBooleanRoot->addChildNode(new TextNode('2'));
		$treeBoolean = new BooleanNode($treeBooleanRoot);
		$simpleRoot = new RootNode();
		$simpleRoot->addChildNode(new TextNode('foobar'));
		$multiRoot = new RootNode();
		$multiRoot->addChildNode(new TextNode('foo'));
		$multiRoot->addChildNode(new TextNode('bar'));
		$multiRoot->addChildNode(new TextNode('baz'));
		return array(
			array(
				new ObjectAccessorNode('_all'),
				'$renderingContext->getVariableProvider()->getAll()'
			),
			array(
				new ObjectAccessorNode('foo.bar'),
				'$renderingContext->getVariableProvider()->getByPath(\'foo.bar\', $array0)'
			),
			array(
				new ObjectAccessorNode('foo.bar', array('array', 'array')),
				'$renderingContext->getVariableProvider()[\'foo\'][\'bar\']'
			),
			array(
				new BooleanNode(new TextNode('TRUE')),
				'\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode::evaluateStack($renderingContext, $array0)'
			),
			array(
				new BooleanNode(new TextNode('1 = 1')),
				'\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode::evaluateStack($renderingContext, $array0)'
			),
			array(
				$treeBoolean,
				'\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode::evaluateStack($renderingContext, $array0)'
			),
			array(
				new TernaryExpressionNode('1 ? 2 : 3', array(1, 2, 3)),
				'\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\TernaryExpressionNode::evaluateExpression($renderingContext, $string0, $array1)'
			),
			array(
				new ViewHelperNode(
					new ViewHelperResolver(),
					'f',
					'render',
					array('section' => new TextNode('test'), 'partial' => 'test'),
					new ParsingState()
				),
				'TYPO3Fluid\Fluid\ViewHelpers\RenderViewHelper::renderStatic($arguments0, $renderChildrenClosure1, $renderingContext)'
			),
			array($simpleRoot, '\'foobar\''),
			array($multiRoot, '$output0'),
			array(new TextNode('test'), '\'test\''),
			array(new NumericNode('3'), '3'),
			array(new NumericNode('4.5'), '4.5'),
			array(new ArrayNode(array('foo', 'bar')), '$array0'),
			array(new ArrayNode(array(0, new TextNode('test'), new ArrayNode(array('foo', 'bar')))), '$array0')
		);
	}

}
