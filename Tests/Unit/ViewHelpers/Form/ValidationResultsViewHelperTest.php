<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

include_once(__DIR__ . '/../Fixtures/ConstraintSyntaxTreeNode.php');
require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Test for the Validation Results view helper
 *
 */
class ValidationResultsViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @var \TYPO3\Fluid\ViewHelpers\Form\ValidationResultsViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getMockBuilder('TYPO3\Fluid\ViewHelpers\Form\ValidationResultsViewHelper')
			->setMethods(array('renderChildren'))
			->getMock();
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
	}

	/**
	 * @test
	 */
	public function renderOutputsChildNodesByDefault() {
		$this->request->expects($this->atLeastOnce())->method('getInternalArgument')->with('__submittedArgumentValidationResults')->will($this->returnValue(NULL));
		$this->viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('child nodes'));

		$this->assertSame('child nodes', $this->viewHelper->render());
	}

	/**
	 * @test
	 */
	public function renderAddsValidationResultsToTemplateVariableContainer() {
		$mockValidationResults = $this->getMockBuilder('TYPO3\Flow\Error\Result')->getMock();
		$this->request->expects($this->atLeastOnce())->method('getInternalArgument')->with('__submittedArgumentValidationResults')->will($this->returnValue($mockValidationResults));
		$this->templateVariableContainer->expects($this->at(0))->method('add')->with('validationResults', $mockValidationResults);
		$this->viewHelper->expects($this->once())->method('renderChildren');
		$this->templateVariableContainer->expects($this->at(1))->method('remove')->with('validationResults');

		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderAddsValidationResultsToTemplateVariableContainerWithCustomVariableNameIfSpecified() {
		$mockValidationResults = $this->getMockBuilder('TYPO3\Flow\Error\Result')->getMock();
		$this->request->expects($this->atLeastOnce())->method('getInternalArgument')->with('__submittedArgumentValidationResults')->will($this->returnValue($mockValidationResults));
		$this->templateVariableContainer->expects($this->at(0))->method('add')->with('customName', $mockValidationResults);
		$this->viewHelper->expects($this->once())->method('renderChildren');
		$this->templateVariableContainer->expects($this->at(1))->method('remove')->with('customName');

		$this->viewHelper->render('', 'customName');
	}

	/**
	 * @test
	 */
	public function renderAddsValidationResultsForOnePropertyIfForArgumentIsNotEmpty() {
		$mockPropertyValidationResults = $this->getMockBuilder('TYPO3\Flow\Error\Result')->getMock();
		$mockValidationResults = $this->getMockBuilder('TYPO3\Flow\Error\Result')->getMock();
		$mockValidationResults->expects($this->once())->method('forProperty')->with('somePropertyName')->will($this->returnValue($mockPropertyValidationResults));
		$this->request->expects($this->atLeastOnce())->method('getInternalArgument')->with('__submittedArgumentValidationResults')->will($this->returnValue($mockValidationResults));
		$this->templateVariableContainer->expects($this->at(0))->method('add')->with('validationResults', $mockPropertyValidationResults);
		$this->viewHelper->expects($this->once())->method('renderChildren');
		$this->templateVariableContainer->expects($this->at(1))->method('remove')->with('validationResults');

		$this->viewHelper->render('somePropertyName');
	}

}
?>