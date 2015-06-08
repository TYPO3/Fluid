<?php
namespace NamelessCoder\Fluid\Tests\Unit\Core\Parser\Interceptor;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperResolver;
use NamelessCoder\Fluid\Tests\UnitTestCase;
use NamelessCoder\Fluid\Core\Parser\Interceptor\Escape;
use NamelessCoder\Fluid\Core\Parser\InterceptorInterface;
use NamelessCoder\Fluid\Core\Parser\ParsingState;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use NamelessCoder\Fluid\Core\ViewHelper\AbstractViewHelper;

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
		$this->escapeInterceptor = $this->getAccessibleMock('NamelessCoder\Fluid\Core\Parser\Interceptor\Escape', array('dummy'));
		$this->mockViewHelper = $this->getMockBuilder('NamelessCoder\Fluid\Core\ViewHelper\AbstractViewHelper')->disableOriginalConstructor()->getMock();
		$this->mockNode = $this->getMockBuilder('NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode')->disableOriginalConstructor()->getMock();
		$this->mockParsingState = $this->getMockBuilder('NamelessCoder\Fluid\Core\Parser\ParsingState')
			->setMethods(array('getViewHelperResolver'))->disableOriginalConstructor()->getMock();
		$this->mockParsingState->expects($this->once())->method('getViewHelperResolver')->willReturn(new ViewHelperResolver());
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
	public function processWrapsCurrentViewHelperInHtmlspecialcharsViewHelperOnObjectAccessor() {
		$interceptorPosition = InterceptorInterface::INTERCEPT_OBJECTACCESSOR;
		$mockNode = $this->getMock('NamelessCoder\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', array(), array(), '', FALSE);
		$mockEscapeViewHelper = $this->getMock('NamelessCoder\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper');
		$actualResult = $this->escapeInterceptor->process($mockNode, $interceptorPosition, $this->mockParsingState);
		$this->assertInstanceOf('NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', $actualResult);
	}
}
