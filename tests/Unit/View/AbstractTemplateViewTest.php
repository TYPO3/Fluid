<?php
namespace TYPO3\Fluid\Tests\Unit\View;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3\Fluid\View\AbstractTemplateView;
use TYPO3\Fluid\Core\Rendering\RenderingContext;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3\Fluid\Tests\UnitTestCase;
use TYPO3\Fluid\View\TemplatePaths;

/**
 * Testcase for the TemplateView
 */
class AbstractTemplateViewTest extends UnitTestCase {

	/**
	 * @var AbstractTemplateView
	 */
	protected $view;

	/**
	 * @var RenderingContext
	 */
	protected $renderingContext;

	/**
	 * @var ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * @var TemplateVariableContainer
	 */
	protected $templateVariableContainer;

	/**
	 * Sets up this test case
	 *
	 * @return void
	 */
	public function setUp() {
		$this->templateVariableContainer = $this->getMock('TYPO3\Fluid\Core\Variables\StandardVariableProvider', array('exists', 'remove', 'add'));
		$this->viewHelperVariableContainer = $this->getMock('TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer', array('setView'));
		$this->renderingContext = $this->getMock('TYPO3\Fluid\Core\Rendering\RenderingContext', array('getViewHelperVariableContainer', 'getVariableProvider'));
		$this->renderingContext->expects($this->any())->method('getViewHelperVariableContainer')->will($this->returnValue($this->viewHelperVariableContainer));
		$this->renderingContext->expects($this->any())->method('getVariableProvider')->will($this->returnValue($this->templateVariableContainer));
		$this->view = $this->getMockForAbstractClass('TYPO3\Fluid\View\AbstractTemplateView', array(new TemplatePaths()));
		$this->view->setRenderingContext($this->renderingContext);
	}

	/**
	 * @test
	 */
	public function testGetRenderingContextReturnsExpectedRenderingContext() {
		$result = $this->view->getRenderingContext();
		$this->assertSame($this->renderingContext, $result);
	}

	/**
	 * @test
	 */
	public function viewIsPlacedInViewHelperVariableContainer() {
		$this->viewHelperVariableContainer->expects($this->once())->method('setView')->with($this->view);
		$this->view->setRenderingContext($this->renderingContext);
	}

	/**
	 * @test
	 */
	public function assignAddsValueToTemplateVariableContainer() {
		$this->templateVariableContainer->expects($this->at(0))->method('exists')->with('foo')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(1))->method('add')->with('foo', 'FooValue');
		$this->templateVariableContainer->expects($this->at(2))->method('exists')->with('bar')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(3))->method('add')->with('bar', 'BarValue');

		$this->view
			->assign('foo', 'FooValue')
			->assign('bar', 'BarValue');
	}

	/**
	 * @test
	 */
	public function assignCanOverridePreviouslyAssignedValues() {
		$this->templateVariableContainer->expects($this->at(0))->method('exists')->with('foo')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(1))->method('add')->with('foo', 'FooValue');
		$this->templateVariableContainer->expects($this->at(2))->method('exists')->with('foo')->will($this->returnValue(TRUE));
		$this->templateVariableContainer->expects($this->at(3))->method('remove')->with('foo');
		$this->templateVariableContainer->expects($this->at(4))->method('add')->with('foo', 'FooValueOverridden');

		$this->view->assign('foo', 'FooValue');
		$this->view->assign('foo', 'FooValueOverridden');
	}

	/**
	 * @test
	 */
	public function assignMultipleAddsValuesToTemplateVariableContainer() {
		$this->templateVariableContainer->expects($this->at(0))->method('exists')->with('foo')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(1))->method('add')->with('foo', 'FooValue');
		$this->templateVariableContainer->expects($this->at(2))->method('exists')->with('bar')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(3))->method('add')->with('bar', 'BarValue');
		$this->templateVariableContainer->expects($this->at(4))->method('exists')->with('baz')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(5))->method('add')->with('baz', 'BazValue');

		$this->view
			->assignMultiple(array('foo' => 'FooValue', 'bar' => 'BarValue'))
			->assignMultiple(array('baz' => 'BazValue'));
	}

	/**
	 * @test
	 */
	public function assignMultipleCanOverridePreviouslyAssignedValues() {
		$this->templateVariableContainer->expects($this->at(0))->method('exists')->with('foo')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(1))->method('add')->with('foo', 'FooValue');
		$this->templateVariableContainer->expects($this->at(2))->method('exists')->with('foo')->will($this->returnValue(TRUE));
		$this->templateVariableContainer->expects($this->at(3))->method('remove')->with('foo');
		$this->templateVariableContainer->expects($this->at(4))->method('add')->with('foo', 'FooValueOverridden');
		$this->templateVariableContainer->expects($this->at(5))->method('exists')->with('bar')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->at(6))->method('add')->with('bar', 'BarValue');

		$this->view->assign('foo', 'FooValue');
		$this->view->assignMultiple(array('foo' => 'FooValueOverridden', 'bar' => 'BarValue'));
	}

	/**
	 * @test
	 */
	public function testBuildParserConfigurationReturnsParserConfiguration() {
		$method = new \ReflectionMethod($this->view, 'buildParserConfiguration');
		$method->setAccessible(TRUE);
		$result = $method->invoke($this->view);
		$this->assertInstanceOf('TYPO3\Fluid\Core\Parser\Configuration', $result);
	}

	/**
	 * @test
	 * @dataProvider getRenderSectionExceptionTestValues
	 * @param boolean $compiled
	 * @test
	 */
	public function testRenderSectionThrowsExceptionIfSectionMissingAndNotIgnoringUnknown($compiled) {
		$parsedTemplate = $this->getMockForAbstractClass(
			'TYPO3\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate',
			array(), '', FALSE, FALSE, TRUE,
			array('isCompiled', 'getVariableContainer')
		);
		$parsedTemplate->expects($this->once())->method('isCompiled')->willReturn($compiled);
		$parsedTemplate->expects($this->any())->method('getVariableContainer')->willReturn(new StandardVariableProvider(
			array('sections' => array())
		));
		$view = $this->getMockForAbstractClass(
			'TYPO3\Fluid\View\AbstractTemplateView',
			array(), '', FALSE, FALSE, TRUE,
			array('getCurrentParsedTemplate', 'getCurrentRenderingType', 'getCurrentRenderingContext')
		);
		$view->expects($this->once())->method('getCurrentRenderingContext')->willReturn($this->renderingContext);
		$view->expects($this->once())->method('getCurrentRenderingType')->willReturn(AbstractTemplateView::RENDERING_LAYOUT);
		$view->expects($this->once())->method('getCurrentParsedTemplate')->willReturn($parsedTemplate);
		$this->setExpectedException('TYPO3\\Fluid\\View\\Exception\\InvalidSectionException');
		$view->renderSection('Missing');
	}

	/**
	 * @return array
	 */
	public function getRenderSectionExceptionTestValues() {
		return array(
			array(TRUE),
			array(FALSE)
		);
	}

	/**
	 * @test
	 */
	public function testSetTemplateProcessorsDelegatesToTemplateParser() {
		$view = $this->getMockForAbstractClass('TYPO3\Fluid\View\AbstractTemplateView', array(), '', FALSE, FALSE, TRUE);
		$parser = $this->getMock('TYPO3\\Fluid\\Core\\Parser\\TemplateParser');
		$view->setTemplateParser($parser);
		$parser->expects($this->once())->method('setTemplateProcessors')->with(array());
		$view->setTemplateProcessors(array());
	}

	/**
	 * @test
	 * @dataProvider getRenderSectionCompiledTestValues
	 * @param boolean $exists
	 * @test
	 */
	public function testRenderSectionOnCompiledTemplate($exists) {
		if ($exists) {
			$sectionMethodName = 'section_' . sha1('Section');
		} else {
			$sectionMethodName = 'test';
		}
		$parsedTemplate = $this->getMockForAbstractClass(
			'TYPO3\\Fluid\\Core\\Compiler\\AbstractCompiledTemplate',
			array(), '', FALSE, FALSE, TRUE,
			array('isCompiled', 'getVariableContainer', $sectionMethodName)
		);
		$parsedTemplate->expects($this->once())->method('isCompiled')->willReturn(TRUE);
		$view = $this->getMockForAbstractClass(
			'TYPO3\Fluid\View\AbstractTemplateView',
			array(), '', FALSE, FALSE, TRUE,
			array('getCurrentParsedTemplate', 'getCurrentRenderingType', 'getCurrentRenderingContext')
		);
		$view->expects($this->once())->method('getCurrentRenderingContext')->willReturn($this->renderingContext);
		$view->expects($this->once())->method('getCurrentRenderingType')->willReturn(AbstractTemplateView::RENDERING_LAYOUT);
		$view->expects($this->once())->method('getCurrentParsedTemplate')->willReturn($parsedTemplate);
		$view->renderSection('Section', NULL, TRUE);
	}

	/**
	 * @return array
	 */
	public function getRenderSectionCompiledTestValues() {
		return array(
			array(TRUE),
			array(FALSE)
		);
	}

}
