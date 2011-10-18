<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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
	 * @test
	 */
	public function renderWithoutSpecifiedNameLoopsThroughRootErrors() {
		$this->markTestIncomplete('Sebastian -- TODO after T3BOARD');
		$mockError1 = $this->getMock('TYPO3\FLOW3\Error\Error', array(), array(), '', FALSE);
		$mockError2 = $this->getMock('TYPO3\FLOW3\Error\Error', array(), array(), '', FALSE);
		$this->request->expects($this->atLeastOnce())->method('getErrors')->will($this->returnValue(array($mockError1, $mockError2)));

		$viewHelper = new \TYPO3\Fluid\ViewHelpers\Form\ValidationResultsViewHelper();
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$variableContainer = new \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer(array());
		$viewHelperNode = new \TYPO3\Fluid\ViewHelpers\Fixtures\ConstraintSyntaxTreeNode($variableContainer);
		$viewHelper->setViewHelperNode($viewHelperNode);
		$viewHelper->setTemplateVariableContainer($variableContainer);

		$viewHelper->render();

		$expectedCallProtocol = array(
			array('error' => $mockError1),
			array('error' => $mockError2)
		);
		$this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs');
	}

}
?>