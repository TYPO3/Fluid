<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Link;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 */
class ActionViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * var \TYPO3\Fluid\ViewHelpers\Link\ActionViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Link\ActionViewHelper', array('renderChildren'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 */
	public function renderCorrectlySetsTagNameAndAttributesAndContent() {
		$mockTagBuilder = $this->getMock('TYPO3\Fluid\Core\ViewHelper\TagBuilder', array('setTagName', 'addAttribute', 'setContent'));
		$mockTagBuilder->expects($this->once())->method('setTagName')->with('a');
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('href', 'someUri');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('some content');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$this->uriBuilder->expects($this->any())->method('uriFor')->will($this->returnValue('someUri'));

		$this->viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue('some content'));

		$this->viewHelper->initialize();
		$this->viewHelper->render('index');
	}

	/**
	 * @test
	 */
	public function renderCorrectlyPassesDefaultArgumentsToUriBuilder() {
		$this->uriBuilder->expects($this->once())->method('setSection')->with('');
		$this->uriBuilder->expects($this->once())->method('setArguments')->with(array());
		$this->uriBuilder->expects($this->once())->method('setAddQueryString')->with(FALSE);
		$this->uriBuilder->expects($this->once())->method('setArgumentsToBeExcludedFromQueryString')->with(array());
		$this->uriBuilder->expects($this->once())->method('setFormat')->with('');
		$this->uriBuilder->expects($this->once())->method('uriFor')->with('theActionName', array(), NULL, NULL, NULL);

		$this->viewHelper->initialize();
		$this->viewHelper->render('theActionName');
	}

	/**
	 * @test
	 */
	public function renderCorrectlyPassesAllArgumentsToUriBuilder() {
		$this->uriBuilder->expects($this->once())->method('setSection')->with('someSection');
		$this->uriBuilder->expects($this->once())->method('setArguments')->with(array('additional' => 'Parameters'));
		$this->uriBuilder->expects($this->once())->method('setAddQueryString')->with(TRUE);
		$this->uriBuilder->expects($this->once())->method('setArgumentsToBeExcludedFromQueryString')->with(array('arguments' => 'toBeExcluded'));
		$this->uriBuilder->expects($this->once())->method('setFormat')->with('someFormat');
		$this->uriBuilder->expects($this->once())->method('uriFor')->with('someAction', array('some' => 'argument'), 'someController', 'somePackage', 'someSubpackage');

		$this->viewHelper->initialize();
		$this->viewHelper->render('someAction', array('some' => 'argument'), 'someController', 'somePackage', 'someSubpackage', 'someSection', 'someFormat', array('additional' => 'Parameters'), TRUE, array('arguments' => 'toBeExcluded'));
	}
}

?>
