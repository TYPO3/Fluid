<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\Fluid\Tests\UnitTestCase;
use TYPO3\Fluid\Core\Rendering\RenderingContext;

/**
 * Testcase for ParsingState
 *
 */
class RenderingContextTest extends UnitTestCase {

	/**
	 * @var RenderingContextInterface
	 */
	protected $renderingContext;

	public function setUp() {
		$this->renderingContext = new RenderingContext();
	}

	/**
	 * @test
	 */
	public function templateVariableContainerCanBeReadCorrectly() {
		$templateVariableContainer = $this->getMock('TYPO3\Fluid\Core\Variables\StandardVariableProvider');
		$this->renderingContext->setVariableProvider($templateVariableContainer);
		$this->assertSame($this->renderingContext->getVariableProvider(), $templateVariableContainer, 'Template Variable Container could not be read out again.');
	}

	/**
	 * @test
	 */
	public function viewHelperVariableContainerCanBeReadCorrectly() {
		$viewHelperVariableContainer = $this->getMock('TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$this->renderingContext->injectViewHelperVariableContainer($viewHelperVariableContainer);
		$this->assertSame($viewHelperVariableContainer, $this->renderingContext->getViewHelperVariableContainer());
	}
}
