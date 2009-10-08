<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Rendering;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @version $Id$
 */
/**
 * Testcase for ParsingState
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class RenderingContextTest extends \F3\Testing\BaseTestCase {

	/**
	 * Parsing state
	 * @var \F3\Fluid\Core\Rendering\RenderingContext
	 */
	protected $renderingContext;

	public function setUp() {
		$this->renderingContext = new \F3\Fluid\Core\Rendering\RenderingContext();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function templateVariableContainerCanBeReadCorrectly() {
		$templateVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\TemplateVariableContainer');
		$this->renderingContext->setTemplateVariableContainer($templateVariableContainer);
		$this->assertSame($this->renderingContext->getTemplateVariableContainer(), $templateVariableContainer, 'Template Variable Container could not be read out again.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function controllerContextCanBeReadCorrectly() {
		$controllerContext = $this->getMock('F3\FLOW3\MVC\Controller\ControllerContext');
		$this->renderingContext->setControllerContext($controllerContext);
		$this->assertSame($this->renderingContext->getControllerContext(), $controllerContext);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderingConfiguationCanBeReadCorrectly() {
		$renderingConfiguration = $this->getMock('F3\Fluid\Core\Rendering\RenderingConfiguration');
		$this->renderingContext->setRenderingConfiguration($renderingConfiguration);
		$this->assertSame($this->renderingContext->getRenderingConfiguration(), $renderingConfiguration);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function ObjectAccessorPostProcessorEnabledCanBeReadCorrectly() {
		$this->assertTrue($this->renderingContext->isObjectAccessorPostProcessorEnabled(), 'The default argument evaluation was not FALSE');
		$this->renderingContext->setObjectAccessorPostProcessorEnabled(FALSE);
		$this->assertFalse($this->renderingContext->isObjectAccessorPostProcessorEnabled());
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function viewHelperVariableContainerCanBeReadCorrectly() {
		$viewHelperVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$this->renderingContext->setViewHelperVariableContainer($viewHelperVariableContainer);
		$this->assertSame($viewHelperVariableContainer, $this->renderingContext->getViewHelperVariableContainer());
	}
}

?>