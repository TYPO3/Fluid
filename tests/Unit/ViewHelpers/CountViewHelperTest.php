<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\ViewHelpers\CountViewHelper;

/**
 * Testcase for CountViewHelper
 */
class CountViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var CountViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getAccessibleMock(CountViewHelper::class, array('buildRenderChildrenClosure'));
	}

	/**
	 * @test
	 */
	public function renderReturnsNumberOfElementsInAnArray() {
		$this->viewHelper->expects($this->never())->method('buildRenderChildrenClosure');
		$expectedResult = 3;
		$this->arguments = array('subject' => array('foo', 'bar', 'Baz'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$actualResult = $this->viewHelper->initializeArgumentsAndRender();
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function renderReturnsNumberOfElementsInAnArrayObject() {
		$this->viewHelper->expects($this->never())->method('buildRenderChildrenClosure');
		$expectedResult = 2;
		$this->arguments = array('subject' => new \ArrayObject(array('foo', 'bar')));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$actualResult = $this->viewHelper->initializeArgumentsAndRender();
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function renderReturnsZeroIfGivenArrayIsEmpty() {
		$this->viewHelper->expects($this->never())->method('buildRenderChildrenClosure');
		$expectedResult = 0;
		$this->arguments = array('subject' => array());
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$actualResult = $this->viewHelper->render();
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function renderUsesChildrenAsSubjectIfGivenSubjectIsNull() {
		$this->viewHelper->expects($this->once())->method('buildRenderChildrenClosure')
			->will($this->returnValue(function() { return array('foo', 'baz', 'bar'); }));
		$expectedResult = 3;
		$this->arguments = array('subject' => NULL);
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$actualResult = $this->viewHelper->initializeArgumentsAndRender();
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function renderReturnsZeroIfGivenSubjectIsNullAndRenderChildrenReturnsNull() {
		$this->viewHelper->expects($this->once())->method('buildRenderChildrenClosure')
			->will($this->returnValue(function() { return NULL; }));
		$this->viewHelper->setArguments(array('subject' => NULL));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$expectedResult = 0;
		$actualResult = $this->viewHelper->initializeArgumentsAndRender();
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	public function renderThrowsExceptionIfGivenSubjectIsNotCountable() {
		$object = new \stdClass();
		$this->viewHelper->setArguments(array('subject' => $object));
		$this->viewHelper->initializeArgumentsAndRender();
	}
}
