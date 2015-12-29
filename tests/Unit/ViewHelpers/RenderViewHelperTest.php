<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\ViewHelpers\RenderViewHelper;

/**
 * Testcase for RenderViewHelper
 */
class RenderViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var RenderViewHelper
	 */
	protected $subject;

	/**
	 * @var TemplateView
	 */
	protected $view;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->subject = $this->getMock(RenderViewHelper::class, array('renderChildren'));
		$this->view = $this->getMock(TemplateView::class, array('renderPartial', 'renderSection'));
		$this->view->setRenderingContext($this->renderingContext);
		$container = new ViewHelperVariableContainer();
		$container->setView($this->view);
		$this->renderingContext->setViewHelperVariableContainer($container);
		$this->subject->setRenderingContext($this->renderingContext);
	}

	/**
	 * @test
	 */
	public function testInitializeArgumentsRegistersExpectedArguments() {
		$instance = $this->getMock(RenderViewHelper::class, array('registerArgument'));
		$instance->expects($this->at(0))->method('registerArgument')->with('section', 'string', $this->anything(), FALSE, NULL);
		$instance->expects($this->at(1))->method('registerArgument')->with('partial', 'string', $this->anything(), FALSE, NULL);
		$instance->expects($this->at(2))->method('registerArgument')->with('arguments', 'array', $this->anything(), FALSE, array());
		$instance->expects($this->at(3))->method('registerArgument')->with('optional', 'boolean', $this->anything(), FALSE, FALSE);
		$instance->expects($this->at(4))->method('registerArgument')->with('default', 'mixed', $this->anything(), FALSE, NULL);
		$instance->expects($this->at(5))->method('registerArgument')->with('contentAs', 'string', $this->anything(), FALSE, NULL);
		$instance->initializeArguments();
	}

	/**
	 * @test
	 * @dataProvider getRenderTestValues
	 * @param array $arguments
	 * @param string|NULL $expectedViewMethod
	 */
	public function testRender(array $arguments, $expectedViewMethod) {
		if ($expectedViewMethod !== NULL) {
			$this->view->expects($this->once())->method($expectedViewMethod)->willReturn('');
		}
		$this->subject->expects($this->any())->method('renderChildren')->willReturn(NULL);
		$this->subject->setArguments($arguments);
		$this->subject->render();
	}

	/**
	 * @return array
	 */
	public function getRenderTestValues() {
		return array(
			array(
				array('partial' => NULL, 'section' => NULL, 'arguments' => array(), 'optional' => FALSE, 'default' => NULL, 'contentAs' => NULL),
				NULL
			),
			array(
				array('partial' => 'foo-partial', 'section' => NULL, 'arguments' => array(), 'optional' => FALSE, 'default' => NULL, 'contentAs' => NULL),
				'renderPartial'
			),
			array(
				array('partial' => 'foo-partial', 'section' => 'foo-section', 'arguments' => array(), 'optional' => FALSE, 'default' => NULL, 'contentAs' => NULL),
				'renderPartial'
			),
			array(
				array('partial' => NULL, 'section' => 'foo-section', 'arguments' => array(), 'optional' => FALSE, 'default' => NULL, 'contentAs' => NULL),
				'renderSection'
			),
		);
	}

	/**
	 * @test
	 */
	public function testRenderWithDefautReturnsDefaultIfContentEmpty() {
		$this->view->expects($this->once())->method('renderPartial')->willReturn('');
		$this->subject->expects($this->any())->method('renderChildren')->willReturn(NULL);
		$this->subject->setArguments(
			array(
				'partial' => 'test',
				'section' => NULL,
				'arguments' => array(),
				'optional' => TRUE,
				'default' => 'default-foobar',
				'contentAs' => NULL
			)
		);
		$output = $this->subject->render();
		$this->assertEquals('default-foobar', $output);
	}

	/**
	 * @test
	 */
	public function testRenderSupportsContentAs() {
		$variables = array('foo' => 'bar', 'foobar' => 'tagcontent-foobar');
		$this->view->expects($this->once())->method('renderPartial')->with('test1', 'test2', $variables, TRUE)->willReturn('baz');
		$this->subject->expects($this->any())->method('renderChildren')->willReturn('tagcontent-foobar');
		$this->subject->setArguments(
			array(
				'partial' => 'test1',
				'section' => 'test2',
				'arguments' => array(
					'foo' => 'bar'
				),
				'optional' => TRUE,
				'default' => NULL,
				'contentAs' => 'foobar'
			)
		);
		$output = $this->subject->render();
		$this->assertEquals('baz', $output);
	}

}
