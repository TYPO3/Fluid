<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Parser\Interceptor;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for Interceptor\Escape
 */
class EscapeTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Fluid\Core\Parser\Interceptor\Escape
	 */
	protected $escapeInterceptor;

	/**
	 * @var \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper
	 */
	protected $mockViewHelper;

	/**
	 * @var \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
	 */
	protected $mockNode;

	/**
	 * @var \TYPO3\Fluid\Core\Parser\ParsingState
	 */
	protected $mockParsingState;

	public function setUp() {
		$this->escapeInterceptor = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\Interceptor\Escape', array('dummy'));
		$this->mockViewHelper = $this->getMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper');
		$this->mockNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array(), array(), '', FALSE);
		$this->mockParsingState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
	}

	/**
	 * @test
	 */
	public function processDoesNotDisableEscapingInterceptorByDefault() {
		$interceptorPosition = \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER;
		$this->mockViewHelper->expects($this->once())->method('isEscapingInterceptorEnabled')->will($this->returnValue(TRUE));
		$this->mockNode->expects($this->once())->method('getUninitializedViewHelper')->will($this->returnValue($this->mockViewHelper));

		$this->assertTrue($this->escapeInterceptor->_get('interceptorEnabled'));
		$this->escapeInterceptor->process($this->mockNode, $interceptorPosition, $this->mockParsingState);
		$this->assertTrue($this->escapeInterceptor->_get('interceptorEnabled'));
	}

	/**
	 * @test
	 */
	public function processDisablesEscapingInterceptorIfViewHelperDisablesIt() {
		$interceptorPosition = \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER;
		$this->mockViewHelper->expects($this->once())->method('isEscapingInterceptorEnabled')->will($this->returnValue(FALSE));
		$this->mockNode->expects($this->once())->method('getUninitializedViewHelper')->will($this->returnValue($this->mockViewHelper));

		$this->assertTrue($this->escapeInterceptor->_get('interceptorEnabled'));
		$this->escapeInterceptor->process($this->mockNode, $interceptorPosition, $this->mockParsingState);
		$this->assertFalse($this->escapeInterceptor->_get('interceptorEnabled'));
	}

	/**
	 * @test
	 */
	public function processReenablesEscapingInterceptorOnClosingViewHelperTagIfItWasDisabledBefore() {
		$interceptorPosition = \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;

		$this->escapeInterceptor->_set('interceptorEnabled', FALSE);
		$this->escapeInterceptor->_set('viewHelperNodesWhichDisableTheInterceptor', array($this->mockNode));

		$this->escapeInterceptor->process($this->mockNode, $interceptorPosition, $this->mockParsingState);
		$this->assertTrue($this->escapeInterceptor->_get('interceptorEnabled'));
	}

	/**
	 * @test
	 */
	public function processWrapsCurrentViewHelperInHtmlspecialcharsViewHelperOnObjectAccessor() {
		$interceptorPosition = \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_OBJECTACCESSOR;
		$mockNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', array(), array(), '', FALSE);
		$mockEscapeViewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper');
		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->at(0))->method('get')->with('TYPO3\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper')->will($this->returnValue($mockEscapeViewHelper));
		$mockObjectManager->expects($this->at(1))->method('get')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', $mockEscapeViewHelper, array('value' => $mockNode))->will($this->returnValue($this->mockNode));
		$this->escapeInterceptor->injectObjectManager($mockObjectManager);

		$actualResult = $this->escapeInterceptor->process($mockNode, $interceptorPosition, $this->mockParsingState);
		$this->assertSame($this->mockNode, $actualResult);
	}
}
