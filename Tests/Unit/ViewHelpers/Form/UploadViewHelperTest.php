<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Resource\Resource;

require_once(__DIR__ . '/Fixtures/EmptySyntaxTreeNode.php');
require_once(__DIR__ . '/Fixtures/Fixture_UserDomainClass.php');
require_once(__DIR__ . '/FormFieldViewHelperBaseTestcase.php');

/**
 * Test for the "Upload" Form view helper
 */
class UploadViewHelperTest extends \TYPO3\Fluid\Tests\Unit\ViewHelpers\Form\FormFieldViewHelperBaseTestcase {

	/**
	 * @var \TYPO3\Fluid\ViewHelpers\Form\UploadViewHelper
	 */
	protected $viewHelper;

	/**
	 * @var \TYPO3\Flow\Property\PropertyMapper
	 */
	protected $mockPropertyMapper;

	/**
	 * @var \TYPO3\Flow\Error\Result
	 */
	protected $mockMappingResult;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Form\UploadViewHelper', array('setErrorClassAttribute', 'registerFieldNameForFormTokenGeneration', 'getMappingResultsForProperty'));
		$this->mockMappingResult = $this->getMock('TYPO3\Flow\Error\Result');
		$this->viewHelper->expects($this->any())->method('getMappingResultsForProperty')->will($this->returnValue($this->mockMappingResult));
		$this->mockPropertyMapper = $this->getMock('TYPO3\Flow\Property\PropertyMapper');
		$this->viewHelper->_set('propertyMapper', $this->mockPropertyMapper);
		$this->arguments['name'] = '';
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 */
	public function renderCorrectlySetsTagName() {
		$this->tagBuilder->expects($this->once())->method('setTagName')->with('input');

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderCorrectlySetsTypeNameAndValueAttributes() {
		$mockTagBuilder = $this->getMock('TYPO3\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->at(0))->method('addAttribute')->with('type', 'file');
		$mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('name', 'someName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('someName');
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$arguments = array(
			'name' => 'someName'
		);

		$this->viewHelper->setArguments($arguments);
		$this->viewHelper->setViewHelperNode(new \TYPO3\Fluid\ViewHelpers\Fixtures\EmptySyntaxTreeNode());
		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function renderCallsSetErrorClassAttribute() {
		$this->viewHelper->expects($this->once())->method('setErrorClassAttribute');
		$this->viewHelper->render();
	}

	/**
	 * @test
	 */
	public function hiddenFieldsAreNotRenderedByDefault() {
		$expectedResult = '';
		$this->viewHelper->initialize();
		$actualResult = $this->viewHelper->render();
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function hiddenFieldsContainDataOfTheSpecifiedResource() {
		$resource = new Resource();

		$mockPersistenceManager = $this->getMock('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->atLeastOnce())->method('getIdentifierByObject')->with($resource)->will($this->returnValue('79ecda60-1a27-69ca-17bf-a5d9e80e6c39'));

		$this->viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$this->viewHelper->setArguments(array('name' => '[foo]', 'value' => $resource));
		$this->viewHelper->initialize();

		$expectedResult = '<input type="hidden" name="[foo][__identity][originallySubmittedResource][__identity]" value="79ecda60-1a27-69ca-17bf-a5d9e80e6c39" />';
		$actualResult = $this->viewHelper->render();

		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function hiddenFieldsAreNotRenderedIfPropertyMappingErrorsOccurred() {
		$resource = new Resource();
		$this->viewHelper->expects($this->any())->method('getValue')->will($this->returnValue($resource));

		$this->mockMappingResult->expects($this->atLeastOnce())->method('hasErrors')->will($this->returnValue(TRUE));

		$expectedResult = '';
		$this->viewHelper->initialize();
		$actualResult = $this->viewHelper->render();
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function hiddenFieldsContainDataOfAPreviouslyUploadedResource() {
		$resourceData = array(
				'error' => \UPLOAD_ERR_NO_FILE,
				'submittedFile' => array(
						'filename' => 'SomeFilename',
				),
				'originallySubmittedResource' => array(
						'__identity' => '79ecda60-1a27-69ca-17bf-a5d9e80e6c39'
				)
		);

		$resource = new Resource();
		$mockPersistenceManager = $this->getMock('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->once())->method('getIdentifierByObject')->with($resource)->will($this->returnValue('79ecda60-1a27-69ca-17bf-a5d9e80e6c39'));
		$this->viewHelper->_set('persistenceManager', $mockPersistenceManager);

		$this->viewHelper->setArguments(array('name' => '[foo]', 'value' => $resourceData));
		$this->viewHelper->initialize();

		$this->mockPropertyMapper->expects($this->atLeastOnce())->method('convert')->with($resourceData, 'TYPO3\Flow\Resource\Resource')->will($this->returnValue($resource));

		$expectedResult = '<input type="hidden" name="[foo][originallySubmittedResource][__identity]" value="79ecda60-1a27-69ca-17bf-a5d9e80e6c39" />';
		$this->viewHelper->initialize();
		$actualResult = $this->viewHelper->render();
		$this->assertSame($expectedResult, $actualResult);
	}
}
