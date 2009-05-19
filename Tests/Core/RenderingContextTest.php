<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core;

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
 * @package Fluid
 * @subpackage Tests
 * @version $Id: ParsingStateTest.php 2247 2009-05-18 20:02:37Z sebastian $
 */
/**
 * Testcase for ParsingState
 *
 * @package Fluid
 * @subpackage Tests
 * @version $Id: ParsingStateTest.php 2247 2009-05-18 20:02:37Z sebastian $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class RenderingContextTest extends \F3\Testing\BaseTestCase {

	/**
	 * Parsing state
	 * @var \F3\Fluid\Core\RenderingContext
	 */
	protected $renderingContext;

	public function setUp() {
		$this->renderingContext = new \F3\Fluid\Core\RenderingContext();
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
}

?>