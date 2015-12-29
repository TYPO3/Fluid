<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\Interceptor;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Interceptor\Escape;
use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper;

/**
 * Testcase for Interceptor\Escape
 */
class EscapeTest extends UnitTestCase {

	/**
	 * @var Escape|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $escapeInterceptor;

	/**
	 * @var AbstractViewHelper|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockViewHelper;

	/**
	 * @var ViewHelperNode|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockNode;

	/**
	 * @var ParsingState|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockParsingState;

	public function setUp() {
		$this->escapeInterceptor = $this->getAccessibleMock(Escape::class, array('dummy'));
		$this->mockViewHelper = $this->getMockBuilder(AbstractViewHelper::class)->disableOriginalConstructor()->getMock();
		$this->mockNode = $this->getMockBuilder(ViewHelperNode::class)->disableOriginalConstructor()->getMock();
		$this->mockParsingState = $this->getMockBuilder(ParsingState::class)
			->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
	}

	/**
	 * @test
	 */
	public function processDoesNotDisableEscapingInterceptorByDefault() {
		$interceptorPosition = InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER;
		$this->mockViewHelper->expects($this->once())->method('isChildrenEscapingEnabled')->will($this->returnValue(TRUE));
		$this->mockNode->expects($this->once())->method('getUninitializedViewHelper')->will($this->returnValue($this->mockViewHelper));

		$this->assertTrue($this->escapeInterceptor->_get('childrenEscapingEnabled'));
		$this->escapeInterceptor->process($this->mockNode, $interceptorPosition, $this->mockParsingState);
		$this->assertTrue($this->escapeInterceptor->_get('childrenEscapingEnabled'));
	}

	/**
	 * @test
	 */
	public function processDisablesEscapingInterceptorIfViewHelperDisablesIt() {
		$interceptorPosition = InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER;
		$this->mockViewHelper->expects($this->once())->method('isChildrenEscapingEnabled')->will($this->returnValue(FALSE));
		$this->mockNode->expects($this->once())->method('getUninitializedViewHelper')->will($this->returnValue($this->mockViewHelper));

		$this->assertTrue($this->escapeInterceptor->_get('childrenEscapingEnabled'));
		$this->escapeInterceptor->process($this->mockNode, $interceptorPosition, $this->mockParsingState);
		$this->assertFalse($this->escapeInterceptor->_get('childrenEscapingEnabled'));
	}

	/**
	 * @test
	 */
	public function processReenablesEscapingInterceptorOnClosingViewHelperTagIfItWasDisabledBefore() {
		$interceptorPosition = InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;
		$this->mockViewHelper->expects($this->any())->method('isOutputEscapingEnabled')->will($this->returnValue(FALSE));
		$this->mockNode->expects($this->any())->method('getUninitializedViewHelper')->will($this->returnValue($this->mockViewHelper));

		$this->escapeInterceptor->_set('childrenEscapingEnabled', FALSE);
		$this->escapeInterceptor->_set('viewHelperNodesWhichDisableTheInterceptor', array($this->mockNode));

		$this->escapeInterceptor->process($this->mockNode, $interceptorPosition, $this->mockParsingState);
		$this->assertTrue($this->escapeInterceptor->_get('childrenEscapingEnabled'));
	}

	/**
	 * @test
	 */
	public function processWrapsCurrentViewHelperInEscapeNode() {
		$interceptorPosition = InterceptorInterface::INTERCEPT_OBJECTACCESSOR;
		$mockNode = $this->getMock(ObjectAccessorNode::class, array(), array(), '', FALSE);
		$actualResult = $this->escapeInterceptor->process($mockNode, $interceptorPosition, $this->mockParsingState);
		$this->assertInstanceOf(EscapingNode::class, $actualResult);
	}
}
