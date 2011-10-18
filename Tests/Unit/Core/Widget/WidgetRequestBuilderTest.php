<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Widget;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for WidgetRequestBuilder
 *
 */
class WidgetRequestBuilderTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Fluid\Core\Widget\WidgetRequestBuilder
	 */
	protected $widgetRequestBuilder;

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $mockObjectManager;

	/**
	 * @var \TYPO3\Fluid\Core\Widget\WidgetRequest
	 */
	protected $mockWidgetRequest;

	/**
	 * @var \TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder
	 */
	protected $mockAjaxWidgetContextHolder;

	/**
	 * @var \TYPO3\Fluid\Core\Widget\WidgetContext
	 */
	protected $mockWidgetContext;

	/**
	 * @var \TYPO3\FLOW3\Utility\Environment
	 */
	protected $mockEnvironment;

	/**
	 * @var \TYPO3\FLOW3\Security\Cryptography\HashService
	 */
	protected $mockHashService;

	/**
	 * @var array
	 */
	protected $rawGetArguments = array('__widgetId' => 1);

	/**
	 * @var array
	 */
	protected $rawPostArguments = array();

	/**
	 */
	public function setUp() {
		$this->widgetRequestBuilder = $this->getAccessibleMock('TYPO3\Fluid\Core\Widget\WidgetRequestBuilder', array('dummy'));

		$mockRequestUri = $this->getMock('TYPO3\FLOW3\Property\DataType\Uri', array(), array('http://request.uri.invalid/some/widget/request'));
		$mockRequestUri->expects($this->any())->method('getArguments')->will($this->returnCallback(array($this, 'getMockGetArguments')));

		$this->mockWidgetRequest = $this->getMock('TYPO3\FLOW3\MVC\Web\Request');
		$this->mockWidgetRequest->expects($this->any())->method('getRequestUri')->will($this->returnValue($mockRequestUri));
		$this->mockWidgetRequest->expects($this->any())->method('getInternalArgument')->will($this->returnCallback(array($this, 'getMockGetArguments')));

		$this->mockObjectManager = $this->getMock('TYPO3\FLOW3\Object\ObjectManagerInterface');
		$this->mockObjectManager->expects($this->once())->method('get')->with('TYPO3\FLOW3\MVC\Web\Request')->will($this->returnValue($this->mockWidgetRequest));

		$this->widgetRequestBuilder->_set('objectManager', $this->mockObjectManager);

		$this->mockWidgetContext = $this->getMock('TYPO3\Fluid\Core\Widget\WidgetContext');

		$this->mockAjaxWidgetContextHolder = $this->getMock('TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder');
		$this->mockAjaxWidgetContextHolder->expects($this->any())->method('get')->will($this->returnValue($this->mockWidgetContext));
		$this->widgetRequestBuilder->injectAjaxWidgetContextHolder($this->mockAjaxWidgetContextHolder);

		$this->mockHashService = $this->getMock('TYPO3\FLOW3\Security\Cryptography\HashService');
		$this->widgetRequestBuilder->_set('hashService', $this->mockHashService);

		$this->mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);

		$this->mockEnvironment->expects($this->any())->method('getRequestUri')->will($this->returnValue($mockRequestUri));
		$this->mockEnvironment->expects($this->any())->method('getBaseUri')->will($this->returnValue($mockRequestUri));
		$this->mockEnvironment->expects($this->any())->method('getRawPostArguments')->will($this->returnCallback(array($this, 'getMockPostArguments')));
		$this->mockEnvironment->expects($this->any())->method('getUploadedFiles')->will($this->returnValue(array()));
		$this->widgetRequestBuilder->_set('environment', $this->mockEnvironment);
	}

	/**
	 * @return array
	 */
	public function getMockGetArguments($argumentName = NULL) {
		if ($argumentName !== NULL) {
			return isset($this->rawGetArguments[$argumentName]) ? $this->rawGetArguments[$argumentName] : NULL;
		}
		return $this->rawGetArguments;
	}

	/**
	 * @return array
	 */
	public function getMockPostArguments() {
		return $this->rawPostArguments;
	}

	/**
	 * @test
	 */
	public function buildSetsRequestMethodFromEnvironment() {
		$this->mockEnvironment->expects($this->once())->method('getRequestMethod')->will($this->returnValue('POST'));
		$this->mockWidgetRequest->expects($this->once())->method('setMethod')->with('POST');

		$this->widgetRequestBuilder->build();
	}

	/**
	 * @test
	 */
	public function buildCallsSetArgumentsFromRawRequestData() {
		$this->rawGetArguments = array('@action' => 'foo', '__widgetId' => '123', 'foo' => 'bar');
		$this->rawPostArguments = array('foo' => 'overridden');

		$this->mockWidgetRequest->expects($this->once())->method('getMethod')->will($this->returnValue('POST'));

		$expectedArguments = array('@action' => 'foo', '__widgetId' => '123', 'foo' => 'overridden');
		$this->mockWidgetRequest->expects($this->once())->method('setArguments')->with($expectedArguments);
		$this->widgetRequestBuilder->build();
	}

	/**
	 * @test
	 */
	public function buildGetsWidgetContextFromAjaxWidgetContextHolderIfWidgetIdIsSpecified() {
		$this->rawGetArguments = array('__widgetId' => 'SomeWidgetId');
		$mockAjaxWidgetContextHolder = $this->getMock('TYPO3\Fluid\Core\Widget\AjaxWidgetContextHolder');
		$mockAjaxWidgetContextHolder->expects($this->once())->method('get')->with('SomeWidgetId')->will($this->returnValue($this->mockWidgetContext));
		$this->widgetRequestBuilder->injectAjaxWidgetContextHolder($mockAjaxWidgetContextHolder);

		$this->widgetRequestBuilder->build();
	}

	/**
	 * @test
	 */
	public function buildGetsWidgetContextFromRequestArgumentsIfWidgetIdIsNotSpecified() {
		$serializedWidgetContext = 'O:37:"TYPO3\Fluid\Core\Widget\WidgetContext":0:{}';
		$serializedWidgetContextWithHmac = $serializedWidgetContext . 'TheHmac';
		$this->rawGetArguments = array('__widgetContext' => $serializedWidgetContextWithHmac);
		$this->mockAjaxWidgetContextHolder->expects($this->never())->method('get');
		$this->mockHashService->expects($this->once())->method('validateAndStripHmac')->with($serializedWidgetContextWithHmac)->will($this->returnValue($serializedWidgetContext));

		$this->widgetRequestBuilder->build();
	}

	/**
	 * @test
	 */
	public function buildReturnsRequest() {
		$expected = $this->mockWidgetRequest;
		$actual = $this->widgetRequestBuilder->build();
		$this->assertSame($expected, $actual);
	}
}
?>