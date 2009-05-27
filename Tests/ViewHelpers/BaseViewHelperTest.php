<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/ViewHelperBaseTestcase.php');
/**
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id:$
 */
class BaseViewHelperTest extends \F3\Fluid\ViewHelpers\ViewHelperBaseTestcase {
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderTakesBaseURIFromControllerContext() {
		$baseURI = 'http://typo3.org/';

		$request = $this->getMock('F3\FLOW3\MVC\Web\Request');
		$request->expects($this->any())->method('getBaseURI')->will($this->returnValue($baseURI));

		$this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($request));

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\BaseViewHelper'), array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$expected = '<base href="http://typo3.org/"></base>';
		$actual = $viewHelper->render();
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderAddsFormNameToTemplateVariableContainer() {
		$formName = 'someFormName';

		$viewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\FormViewHelper'), array('renderChildren', 'renderHiddenIdentityField'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$viewHelper->setArguments(new \F3\Fluid\Core\ViewHelper\Arguments(array('name' => $formName)));

		$this->viewHelperVariableContainer->expects($this->once())->method('add')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'formName', $formName);
		$this->viewHelperVariableContainer->expects($this->once())->method('remove')->with('F3\Fluid\ViewHelpers\FormViewHelper', 'formName');
		$viewHelper->render('', array(), NULL, NULL, NULL, NULL);
	}
}
?>