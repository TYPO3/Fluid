<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Rendering;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for ParsingState
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class RenderingContextTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * Parsing state
	 * @var \TYPO3\Fluid\Core\Rendering\RenderingContextInterface
	 */
	protected $renderingContext;

	public function setUp() {
		$this->renderingContext = new \TYPO3\Fluid\Core\Rendering\RenderingContext();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function templateVariableContainerCanBeReadCorrectly() {
		$templateVariableContainer = $this->getMock('TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer');
		$this->renderingContext->injectTemplateVariableContainer($templateVariableContainer);
		$this->assertSame($this->renderingContext->getTemplateVariableContainer(), $templateVariableContainer, 'Template Variable Container could not be read out again.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function controllerContextCanBeReadCorrectly() {
		$controllerContext = $this->getMock('TYPO3\FLOW3\MVC\Controller\ControllerContext', array(), array(), '', FALSE);
		$this->renderingContext->setControllerContext($controllerContext);
		$this->assertSame($this->renderingContext->getControllerContext(), $controllerContext);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function viewHelperVariableContainerCanBeReadCorrectly() {
		$viewHelperVariableContainer = $this->getMock('TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$this->renderingContext->injectViewHelperVariableContainer($viewHelperVariableContainer);
		$this->assertSame($viewHelperVariableContainer, $this->renderingContext->getViewHelperVariableContainer());
	}
}

?>