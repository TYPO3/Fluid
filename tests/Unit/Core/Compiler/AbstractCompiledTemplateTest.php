<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Rendering\RenderingContext;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper;
use TYPO3\Fluid\Tests\UnitTestCase;

/**
 * Class AbstractCompiledTemplateTest
 */
class AbstractCompiledTemplateTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testParentGetVariableContainerMethodReturnsStandardVariableProvider() {
		$instance = $this->getMockForAbstractClass('TYPO3\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$result = $instance->getVariableContainer(new RenderingContext());
		$this->assertInstanceOf('TYPO3\\Fluid\\Core\\Variables\\StandardVariableProvider', $result);
	}

	/**
	 * @test
	 */
	public function testParentRenderMethodReturnsEmptyString() {
		$instance = $this->getMockForAbstractClass('TYPO3\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$result = $instance->render(new RenderingContext());
		$this->assertEquals('', $result);
	}

	/**
	 * @test
	 */
	public function testParentGetLayoutNameMethodReturnsEmptyString() {
		$instance = $this->getMockForAbstractClass('TYPO3\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$result = $instance->getLayoutName(new RenderingContext());
		$this->assertEquals('', $result);
	}

	/**
	 * @test
	 */
	public function testParentHasLayoutMethodReturnsFalse() {
		$instance = $this->getMockForAbstractClass('TYPO3\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$result = $instance->hasLayout();
		$this->assertEquals(FALSE, $result);
	}

	/**
	 * @test
	 */
	public function testGetViewHelperReturnsInstanceOfClassName() {
		$instance = $this->getMockForAbstractClass('TYPO3\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$viewHelper = new TestViewHelper();
		$resolver = $this->getMock(
			'TYPO3\\Fluid\\Core\\ViewHelper\\ViewHelperResolver',
			array('createViewHelperInstanceFromClassName')
		);
		$resolver->expects($this->once())->method('createViewHelperInstanceFromClassName')->willReturn($viewHelper);
		$renderingContext = new RenderingContext();
		$renderingContext->setViewHelperResolver($resolver);
		$result = $instance->getViewHelper(1, $renderingContext, 'TYPO3\\Fluid\\Tests\\Unit\\Core\\Fixtures\\TestViewHelper');
		$this->assertSame($viewHelper, $result);
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

}
