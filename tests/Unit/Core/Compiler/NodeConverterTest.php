<?php
namespace NamelessCoder\Fluid\Tests\Unit\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Compiler\NodeConverter;
use NamelessCoder\Fluid\Core\Compiler\TemplateCompiler;
use NamelessCoder\Fluid\Core\Parser\ParsingState;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\Expression\TernaryExpressionNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\NumericNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\RootNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\TextNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperResolver;
use NamelessCoder\Fluid\Tests\UnitTestCase;

/**
 * Class NodeConverterTest
 */
class NodeConverterTest extends UnitTestCase {

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
		return array(
			array(
				new BooleanNode(new TextNode('TRUE')),
				'\NamelessCoder\Fluid\Core\Parser\SyntaxTree\BooleanNode::evaluateStack($renderingContext, $array0)'
			),
			array(
				new BooleanNode(new TextNode('1 = 1')),
				'\NamelessCoder\Fluid\Core\Parser\SyntaxTree\BooleanNode::evaluateStack($renderingContext, $array0)'
			),
			array(
				$treeBoolean,
				'\NamelessCoder\Fluid\Core\Parser\SyntaxTree\BooleanNode::evaluateStack($renderingContext, $array0)'
			),
			array(
				new TernaryExpressionNode('1 ? 2 : 3', array(1, 2, 3)),
				'\NamelessCoder\Fluid\Core\Parser\SyntaxTree\Expression\TernaryExpressionNode::evaluateExpression($renderingContext, $string0, $array1)'
			),
			array($simpleRoot, '\'foobar\''),
			array(new TextNode('test'), '\'test\''),
			array(new NumericNode('3'), '3'),
			array(new NumericNode('4.5'), '4.5'),
			array(new ArrayNode(array('foo', 'bar')), '$array0'),
			array(new ArrayNode(array(0, new TextNode('test'), new ArrayNode(array('foo', 'bar')))), '$array0'),
		);
	}

}
