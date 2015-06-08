<?php
namespace NamelessCoder\Fluid\Tests\Unit\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Rendering\RenderingContext;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperResolver;
use NamelessCoder\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper;
use NamelessCoder\Fluid\Tests\UnitTestCase;

/**
 * Class AbstractCompiledTemplateTest
 */
class AbstractCompiledTemplateTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testParentGetVariableContainerMethodReturnsStandardVariableProvider() {
		$instance = $this->getMockForAbstractClass('NamelessCoder\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$result = $instance->getVariableContainer(new RenderingContext());
		$this->assertInstanceOf('NamelessCoder\\Fluid\\Core\\Variables\\StandardVariableProvider', $result);
	}

	/**
	 * @test
	 */
	public function testParentRenderMethodReturnsEmptyString() {
		$instance = $this->getMockForAbstractClass('NamelessCoder\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$result = $instance->render(new RenderingContext());
		$this->assertEquals('', $result);
	}

	/**
	 * @test
	 */
	public function testParentGetLayoutNameMethodReturnsEmptyString() {
		$instance = $this->getMockForAbstractClass('NamelessCoder\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$result = $instance->getLayoutName(new RenderingContext());
		$this->assertEquals('', $result);
	}

	/**
	 * @test
	 */
	public function testParentHasLayoutMethodReturnsFalse() {
		$instance = $this->getMockForAbstractClass('NamelessCoder\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$result = $instance->hasLayout();
		$this->assertEquals(FALSE, $result);
	}

	/**
	 * @test
	 */
	public function testGetViewHelperReturnsInstanceOfClassName() {
		$instance = $this->getMockForAbstractClass('NamelessCoder\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$viewHelper = new TestViewHelper();
		$resolver = $this->getMock(
			'NamelessCoder\\Fluid\\Core\\ViewHelper\\ViewHelperResolver',
			array('createViewHelperInstanceFromClassName')
		);
		$resolver->expects($this->once())->method('createViewHelperInstanceFromClassName')->willReturn($viewHelper);
		$renderingContext = new RenderingContext();
		$renderingContext->setViewHelperResolver($resolver);
		$result = $instance->getViewHelper(1, $renderingContext, 'NamelessCoder\\Fluid\\Tests\\Unit\\Core\\Fixtures\\TestViewHelper');
		$this->assertSame($viewHelper, $result);
	}

	/**
	 * @test
	 */
	public function testIsCompilableReturnsFalse() {
		$instance = $this->getMockForAbstractClass('NamelessCoder\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$result = $instance->isCompilable();
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function testIsCompiledReturnsTrue() {
		$instance = $this->getMockForAbstractClass('NamelessCoder\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$result = $instance->isCompiled();
		$this->assertTrue($result);
	}

	/**
	 * @test
	 */
	public function testAddCompiledNamespacesDoesNothing() {
		$instance = $this->getMockForAbstractClass('NamelessCoder\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate');
		$context = new RenderingContext();
		$before = $context->getViewHelperResolver()->getNamespaces();
		$instance->addCompiledNamespaces($context);
		$after = $context->getViewHelperResolver()->getNamespaces();
		$this->assertEquals($before, $after);
	}

}
