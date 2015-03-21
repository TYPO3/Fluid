<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Rendering\RenderingContext;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3\Fluid\Tests\UnitTestCase;

/**
 * Class AbstractCompiledTemplateTest
 */
class AbstractCompiledTemplateTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testGetViewHelperReturnsInstanceOfClassName() {
		$instance = $this->getMockForAbstractClass('TYPO3\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$result = $instance->getViewHelper(1, new RenderingContext(), 'TYPO3\\Fluid\\Tests\\Unit\\Core\\Fixtures\\TestViewHelper');
		$this->assertInstanceOf('TYPO3\\Fluid\\Tests\\Unit\\Core\\Fixtures\\TestViewHelper', $result);
	}

	/**
	 * @test
	 */
	public function testIsCompilableReturnsFalse() {
		$instance = $this->getMockForAbstractClass('TYPO3\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$result = $instance->isCompilable();
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function testIsCompiledReturnsTrue() {
		$instance = $this->getMockForAbstractClass('TYPO3\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$result = $instance->isCompiled();
		$this->assertTrue($result);
	}

	/**
	 * @test
	 */
	public function testSetViewHelperResolverSetsProperty() {
		$resolver = new ViewHelperResolver();
		$instance = $this->getMockForAbstractClass('TYPO3\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$instance->setViewHelperResolver($resolver);
		$this->assertAttributeSame($resolver, 'viewHelperResolver', $instance);
	}

}
