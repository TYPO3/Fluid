<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\CaseViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\DefaultCaseViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\SwitchViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;

/**
 * Testcase for SwitchViewHelper
 */
class SwitchViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var SwitchViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getMock(SwitchViewHelper::class, array('renderChildren'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
	}

	/**
	 * @test
	 */
	public function renderSetsSwitchExpressionInViewHelperVariableContainer() {
		$switchExpression = new \stdClass();
		$this->viewHelper->setArguments(array('expression' => $switchExpression));
		$this->viewHelper->initializeArgumentsAndRender();
	}

	/**
	 * @test
	 */
	public function renderRemovesSwitchExpressionFromViewHelperVariableContainerAfterInvocation() {
		$this->viewHelper->setArguments(array('expression' => 'switchExpression'));
		$this->viewHelper->initializeArgumentsAndRender();
	}

	/**
	 * @param NodeInterface[] $childNodes
	 * @param array $variables
	 * @param mixed $expected
	 * @test
	 * @dataProvider getRetrieveContentFromChildNodesTestValues
	 */
	public function retrieveContentFromChildNodesProcessesChildNodesCorrectly(array $childNodes, array $variables, $expected) {
		$instance = $this->getAccessibleMock(SwitchViewHelper::class, array('dummy'));
		$context = new RenderingContextFixture();
		$context->getViewHelperVariableContainer()->addOrUpdate(SwitchViewHelper::class, 'break', FALSE);
		foreach ($variables as $name => $value) {
			$context->getViewHelperVariableContainer()->addOrUpdate(SwitchViewHelper::class, $name, $value);
		}
		$instance->_set('viewHelperVariableContainer', $context->getViewHelperVariableContainer());
		$instance->_set('renderingContext', $context);
		$method = new \ReflectionMethod(SwitchViewHelper::class, 'retrieveContentFromChildNodes');
		$method->setAccessible(TRUE);
		$result = $method->invokeArgs($instance, array($childNodes));
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getRetrieveContentFromChildNodesTestValues() {
		$matchingNode = $this->getMock(ViewHelperNode::class, array('evaluate', 'getViewHelperClassName'), array(), '', FALSE);
		$matchingNode->method('getViewHelperClassName')->willReturn(CaseViewHelper::class);
		$matchingNode->method('evaluate')->willReturn('foo');
		$notMatchingNode = $this->getMock(ViewHelperNode::class, array('evaluate', 'getViewHelperClassName'), array(), '', FALSE);
		$notMatchingNode->method('getViewHelperClassName')->willReturn(CaseViewHelper::class);
		$notMatchingNode->method('evaluate')->willReturn('');
		$notMatchingNode->method('getViewHelperClassName')->willReturn(CaseViewHelper::class);
		$defaultCaseNode = $this->getMock(ViewHelperNode::class, array('evaluate', 'getViewHelperClassName'), array(), '', FALSE);
		$defaultCaseNode->method('evaluate')->willReturn('default');
		$defaultCaseNode->method('getViewHelperClassName')->willReturn(DefaultCaseViewHelper::class);
		$textNode = $this->getMock(TextNode::class, array(), array(), '', FALSE);
		$objectAccessorNode = $this->getMock(ObjectAccessorNode::class, array(), array(), '', FALSE);
		return array(
			'empty switch' => array(array(), array('switchExpression' => FALSE), NULL),
			'single case matching' => array(array(clone $matchingNode), array('switchExpression' => 'foo'), 'foo'),
			'two case without break' => array(array(clone $matchingNode, clone $notMatchingNode), array('switchExpression' => 'foo'), ''),
			'single case not matching with default last' => array(array(clone $matchingNode, clone $defaultCaseNode), array('switchExpression' => 'bar'), 'default'),
			'skips non-ViewHelper nodes' => array(array($textNode, $objectAccessorNode, clone $matchingNode), array('switchExpression' => 'foo'), 'foo')
		);
	}

	/**
	 * @test
	 */
	public function retrieveContentFromChildNodesReturnsBreaksOnBreak() {
		$instance = $this->getAccessibleMock(SwitchViewHelper::class, array('dummy'));
		$context = new RenderingContextFixture();
		$context->getViewHelperVariableContainer()->addOrUpdate(SwitchViewHelper::class, 'switchExpression', 'foo');
		$context->getViewHelperVariableContainer()->addOrUpdate(SwitchViewHelper::class, 'break', FALSE);
		$instance->_set('viewHelperVariableContainer', $context->getViewHelperVariableContainer());
		$instance->_set('renderingContext', $context);
		$matchingCaseViewHelper = new CaseViewHelper();
		$matchingCaseViewHelper->setRenderChildrenClosure(function() { return 'foo-childcontent'; });
		$breakingMatchingCaseNode = $this->getAccessibleMock(ViewHelperNode::class, array('getViewHelperClassName', 'getUninitializedViewHelper'), array(), '', FALSE);
		$breakingMatchingCaseNode->_set('arguments', array('value' => 'foo'));
		$breakingMatchingCaseNode->_set('uninitializedViewHelper', $matchingCaseViewHelper);
		$breakingMatchingCaseNode->method('getViewHelperClassName')->willReturn(CaseViewHelper::class);
		$defaultCaseNode = $this->getMock(ViewHelperNode::class, array('getViewHelperClassName', 'evaluate'), array(), '', FALSE);
		$defaultCaseNode->method('getViewHelperClassName')->willReturn(DefaultCaseViewHelper::class);
		$defaultCaseNode->expects($this->never())->method('evaluate');

		$method = new \ReflectionMethod(SwitchViewHelper::class, 'retrieveContentFromChildNodes');
		$method->setAccessible(TRUE);
		$result = $method->invokeArgs($instance, array(array($breakingMatchingCaseNode, $defaultCaseNode)));
		$this->assertEquals('foo-childcontent', $result);
	}
}
