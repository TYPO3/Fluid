<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3Fluid\Fluid\ViewHelpers\Format\PrintfViewHelper;

/**
 * Test for \TYPO3Fluid\Fluid\ViewHelpers\Format\PrintfViewHelper
 */
class PrintfViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var \TYPO3Fluid\Fluid\ViewHelpers\Format\PrintfViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getMock(PrintfViewHelper::class, array('renderChildren'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
	}

	/**
	 * @test
	 */
	public function viewHelperCanUseArrayAsArgument() {
		$this->viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('%04d-%02d-%02d'));
		$this->viewHelper->setArguments(array('value' => NULL, 'arguments' => array('year' => 2009, 'month' => 4, 'day' => 5)));
		$actualResult = $this->viewHelper->initializeArgumentsAndRender();
		$this->assertEquals('2009-04-05', $actualResult);
	}

	/**
	 * @test
	 */
	public function viewHelperCanSwapMultipleArguments() {
		$this->viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('%2$s %1$d %3$s %2$s'));
		$this->viewHelper->setArguments(array('value' => NULL, 'arguments' => array(123, 'foo', 'bar')));
		$actualResult = $this->viewHelper->initializeArgumentsAndRender();
		$this->assertEquals('foo 123 bar foo', $actualResult);
	}
}
