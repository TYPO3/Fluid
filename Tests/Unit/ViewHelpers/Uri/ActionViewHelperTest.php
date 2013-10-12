<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Uri;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Testcase for the action uri view helper
 *
 */
class ActionViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * var \TYPO3\Fluid\ViewHelpers\Uri\ActionViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = new \TYPO3\Fluid\ViewHelpers\Uri\ActionViewHelper();
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 */
	public function renderReturnsUriReturnedFromUriBuilder() {
		$this->uriBuilder->expects($this->any())->method('uriFor')->will($this->returnValue('some/uri'));

		$this->viewHelper->initialize();
		$actualResult = $this->viewHelper->render('index');

		$this->assertEquals('some/uri', $actualResult);
	}

	/**
	 * @test
	 */
	public function renderCorrectlyPassesDefaultArgumentsToUriBuilder() {
		$this->uriBuilder->expects($this->once())->method('setSection')->with('');
		$this->uriBuilder->expects($this->once())->method('setCreateAbsoluteUri')->with(FALSE);
		$this->uriBuilder->expects($this->once())->method('setArguments')->with(array());
		$this->uriBuilder->expects($this->once())->method('setAddQueryString')->with(FALSE);
		$this->uriBuilder->expects($this->once())->method('setArgumentsToBeExcludedFromQueryString')->with(array());
		$this->uriBuilder->expects($this->once())->method('setFormat')->with('');
		$this->uriBuilder->expects($this->once())->method('uriFor')->with('theActionName', array(), NULL, NULL, NULL);

		$this->viewHelper->initialize();
		$this->viewHelper->render('theActionName');
	}

	/**
	 * @test
	 */
	public function renderCorrectlyPassesAllArgumentsToUriBuilder() {
		$this->uriBuilder->expects($this->once())->method('setSection')->with('someSection');
		$this->uriBuilder->expects($this->once())->method('setCreateAbsoluteUri')->with(TRUE);
		$this->uriBuilder->expects($this->once())->method('setArguments')->with(array('additional' => 'Parameters'));
		$this->uriBuilder->expects($this->once())->method('setAddQueryString')->with(TRUE);
		$this->uriBuilder->expects($this->once())->method('setArgumentsToBeExcludedFromQueryString')->with(array('arguments' => 'toBeExcluded'));
		$this->uriBuilder->expects($this->once())->method('setFormat')->with('someFormat');
		$this->uriBuilder->expects($this->once())->method('uriFor')->with('someAction', array('some' => 'argument'), 'someController', 'somePackage', 'someSubpackage');

		$this->viewHelper->initialize();
		$this->viewHelper->render('someAction', array('some' => 'argument'), 'someController', 'somePackage', 'someSubpackage', 'someSection', 'someFormat', array('additional' => 'Parameters'), TRUE, TRUE, array('arguments' => 'toBeExcluded'));
	}

	/**
	 * @test
	 */
	public function renderThrowsViewHelperExceptionIfUriBuilderThrowsFlowException() {
		$this->uriBuilder->expects($this->any())->method('uriFor')->will($this->throwException(new \TYPO3\Flow\Exception('Mock Exception', 12345)));
		$this->viewHelper->initialize();
		try {
			$this->viewHelper->render('someAction');
		} catch (\TYPO3\Fluid\Core\ViewHelper\Exception $exception) {
		}
		$this->assertEquals(12345, $exception->getPrevious()->getCode());
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\ViewHelper\Exception
	 */
	public function renderThrowsExceptionIfUseParentRequestIsSetAndTheCurrentRequestHasNoParentRequest() {
		$this->viewHelper->initialize();
		$this->viewHelper->render('someAction', array(), NULL, NULL, NULL, '', '', array(), FALSE, FALSE, array(), TRUE);
	}

	/**
	 * @test
	 */
	public function renderUsesParentRequestIfUseParentRequestIsSet() {
		$viewHelper = new \TYPO3\Fluid\ViewHelpers\Uri\ActionViewHelper();

		$parentRequest = $this->getMockBuilder('TYPO3\Flow\Mvc\ActionRequest')->disableOriginalConstructor()->getMock();

		$this->request = $this->getMockBuilder('TYPO3\Flow\Mvc\ActionRequest')->disableOriginalConstructor()->getMock();
		$this->request->expects($this->atLeastOnce())->method('isMainRequest')->will($this->returnValue(FALSE));
		$this->request->expects($this->atLeastOnce())->method('getParentRequest')->will($this->returnValue($parentRequest));

		$this->controllerContext = $this->getMock('TYPO3\Flow\Mvc\Controller\ControllerContext', array(), array(), '', FALSE);
		$this->controllerContext->expects($this->any())->method('getUriBuilder')->will($this->returnValue($this->uriBuilder));
		$this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));

		$this->uriBuilder->expects($this->atLeastOnce())->method('setRequest')->with($parentRequest);

		$this->renderingContext->setControllerContext($this->controllerContext);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$viewHelper->render('someAction', array(), NULL, NULL, NULL, '', '', array(), FALSE, FALSE, array(), TRUE);
	}
}
