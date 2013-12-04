<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Error\Error;
use TYPO3\Flow\Error\Result;
use TYPO3\Fluid\ViewHelpers\Validation\IfHasErrorsViewHelper;
use TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase;

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 */
class IfHasErrorsViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var IfHasErrorsViewHelper
	 */
	protected $viewHelper;

	/**
	 */
	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Validation\IfHasErrorsViewHelper', array('renderThenChild', 'renderElseChild'));
		$this->inject($this->viewHelper, 'controllerContext', $this->controllerContext);
		//$this->inject($this->ifAccessViewHelper, 'accessDecisionManager', $this->mockAccessDecisionManager);
	}

	/**
	 * @test
	 */
	public function returnsAndRendersThenChildIfResultsHaveErrors() {
		$result = new Result;
		$result->addError(new Error('I am an error', 1386163707));

		/** @var $requestMock \PHPUnit_Framework_MockObject_MockObject */
		$requestMock = $this->request;
		$requestMock->expects($this->once())->method('getInternalArgument')->with('__submittedArgumentValidationResults')->will($this->returnValue($result));
		$this->viewHelper->expects($this->once())->method('renderThenChild')->will($this->returnValue('ThenChild'));
		$this->assertEquals('ThenChild', $this->viewHelper->render());
	}

	/**
	 * @test
	 */
	public function returnsAndRendersElseChildIfNoValidationResultsArePresentAtAll() {
		$this->viewHelper->expects($this->once())->method('renderElseChild')->will($this->returnValue('ElseChild'));;
		$this->assertEquals('ElseChild', $this->viewHelper->render());
	}

	/**
	 * @test
	 */
	public function queriesResultForPropertyIfPropertyPathIsGiven() {
		$resultMock = $this->getMock('TYPO3\Flow\Error\Result');
		$resultMock->expects($this->once())->method('forProperty')->with('foo.bar.baz')->will($this->returnValue(new Result()));

		/** @var $requestMock \PHPUnit_Framework_MockObject_MockObject */
		$requestMock = $this->request;
		$requestMock->expects($this->once())->method('getInternalArgument')->with('__submittedArgumentValidationResults')->will($this->returnValue($resultMock));

		$this->viewHelper->render('foo.bar.baz');
	}
}