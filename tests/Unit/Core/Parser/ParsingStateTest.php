<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Parser\ParsingState;
use TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\Fluid\Core\Rendering\RenderingContext;
use TYPO3\Fluid\Tests\UnitTestCase;

/**
 * Testcase for ParsingState
 */
class ParsingStateTest extends UnitTestCase {

	/**
	 * Parsing state
	 *
	 * @var ParsingState
	 */
	protected $parsingState;

	public function setUp() {
		$this->parsingState = new ParsingState();
	}

	public function tearDown() {
		unset($this->parsingState);
	}

	/**
	 * @test
	 */
	public function setRootNodeCanBeReadOutAgain() {
		$rootNode = new RootNode();
		$this->parsingState->setRootNode($rootNode);
		$this->assertSame($this->parsingState->getRootNode(), $rootNode, 'Root node could not be read out again.');
	}

	/**
	 * @test
	 */
	public function pushAndGetFromStackWorks() {
		$rootNode = new RootNode();
		$this->parsingState->pushNodeToStack($rootNode);
		$this->assertSame($rootNode, $this->parsingState->getNodeFromStack($rootNode), 'Node returned from stack was not the right one.');
		$this->assertSame($rootNode, $this->parsingState->popNodeFromStack($rootNode), 'Node popped from stack was not the right one.');
	}

	/**
	 * @test
	 */
	public function renderCallsTheRightMethodsOnTheRootNode() {
		$renderingContext = $this->getMock('TYPO3\Fluid\Core\Rendering\RenderingContextInterface');
		$rootNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode');
		$rootNode->expects($this->once())->method('evaluate')->with($renderingContext)->will($this->returnValue('T3DD09 Rock!'));
		$this->parsingState->setRootNode($rootNode);
		$renderedValue = $this->parsingState->render($renderingContext);
		$this->assertEquals($renderedValue, 'T3DD09 Rock!', 'The rendered value of the Root Node is not returned by the ParsingState.');
	}

	/**
	 * @test
	 */
	public function testGetLayoutName() {
		$context = new RenderingContext();
		$context->getVariableProvider()->add('layoutName', 'test');
		$result = $this->parsingState->getLayoutName($context);
		$this->assertEquals('test', $result);
	}

	/**
	 * @test
	 */
	public function testSetCompilableSetsProperty() {
		$this->parsingState->setCompilable(FALSE);
		$this->assertAttributeEquals(FALSE, 'compilable', $this->parsingState);
	}

}
