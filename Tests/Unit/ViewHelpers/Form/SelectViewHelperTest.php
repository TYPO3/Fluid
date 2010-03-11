<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/Fixtures/EmptySyntaxTreeNode.php');
require_once(__DIR__ . '/Fixtures/Fixture_UserDomainClass.php');
require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Test for the "Select" Form view helper
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class SelectViewHelperTest extends \F3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * var \F3\Fluid\ViewHelpers\Form\SelectViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getAccessibleMock('F3\Fluid\ViewHelpers\Form\SelectViewHelper', array('setErrorClassAttribute', 'registerFieldNameForFormTokenGeneration'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function selectCorrectlySetsTagName() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('setTagName'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('setTagName')->with('select');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(array(
			'options' => array()
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function selectCreatesExpectedOptions() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value="value1">label1</option>' . chr(10) . '<option value="value2" selected="selected">label2</option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(array(
			'options' => array(
				'value1' => 'label1',
				'value2' => 'label2'
			),
			'value' => 'value2',
			'name' => 'myName'
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

		/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function anEmptyOptionTagIsRenderedIfOptionsArrayIsEmptyToAssureXhtmlCompatibility() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value=""></option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(array(
			'options' => array(),
			'value' => 'value2',
			'name' => 'myName'
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function OrderOfOptionsIsNotAlteredByDefault() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value="value3">label3</option>' . chr(10) . '<option value="value1">label1</option>' . chr(10) . '<option value="value2" selected="selected">label2</option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(array(
			'options' => array(
				'value3' => 'label3',
				'value1' => 'label1',
				'value2' => 'label2'
			),
			'value' => 'value2',
			'name' => 'myName'
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function optionsAreSortedByLabelIfSortByOptionLabelIsSet() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value="value1">label1</option>' . chr(10) . '<option value="value2" selected="selected">label2</option>' . chr(10) . '<option value="value3">label3</option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(array(
			'options' => array(
				'value3' => 'label3',
				'value1' => 'label1',
				'value2' => 'label2'
			),
			'value' => 'value2',
			'name' => 'myName',
			'sortByOptionLabel' => TRUE
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function multipleSelectCreatesExpectedOptions() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->at(0))->method('addAttribute')->with('multiple', 'multiple');
		$mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('name', 'myName[]');
		$this->viewHelper->expects($this->exactly(3))->method('registerFieldNameForFormTokenGeneration')->with('myName[]');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value="value1" selected="selected">label1</option>' . chr(10) . '<option value="value2">label2</option>' . chr(10) . '<option value="value3" selected="selected">label3</option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(array(
			'options' => array(
				'value1' => 'label1',
				'value2' => 'label2',
				'value3' => 'label3'
			),
			'value' => array('value3', 'value1'),
			'name' => 'myName',
			'multiple' => 'multiple',
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function selectOnDomainObjectsCreatesExpectedOptions() {
		$mockPersistenceBackend = $this->getMock('F3\FLOW3\Persistence\Backend\BackendInterface');
		$mockPersistenceBackend->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue(NULL));

		$mockPersistenceManager = $this->getMock('F3\FLOW3\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getBackend')->will($this->returnValue($mockPersistenceBackend));
		$this->viewHelper->injectPersistenceManager($mockPersistenceManager);

		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value="1">Ingmar</option>' . chr(10) . '<option value="2" selected="selected">Sebastian</option>' . chr(10) . '<option value="3">Robert</option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$user_is = new \F3\Fluid\ViewHelpers\Fixtures\UserDomainClass(1, 'Ingmar', 'Schlecht');
		$user_sk = new \F3\Fluid\ViewHelpers\Fixtures\UserDomainClass(2, 'Sebastian', 'Kurfuerst');
		$user_rl = new \F3\Fluid\ViewHelpers\Fixtures\UserDomainClass(3, 'Robert', 'Lemke');

		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(array(
			'options' => array(
				$user_is,
				$user_sk,
				$user_rl
			),
			'value' => $user_sk,
			'optionValueField' => 'id',
			'optionLabelField' => 'firstName',
			'name' => 'myName'
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function multipleSelectOnDomainObjectsCreatesExpectedOptions() {
		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->at(0))->method('addAttribute')->with('multiple', 'multiple');
		$mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('name', 'myName[]');
		$this->viewHelper->expects($this->exactly(3))->method('registerFieldNameForFormTokenGeneration')->with('myName[]');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value="1" selected="selected">Schlecht</option>' . chr(10) . '<option value="2">Kurfuerst</option>' . chr(10) . '<option value="3" selected="selected">Lemke</option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$user_is = new \F3\Fluid\ViewHelpers\Fixtures\UserDomainClass(1, 'Ingmar', 'Schlecht');
		$user_sk = new \F3\Fluid\ViewHelpers\Fixtures\UserDomainClass(2, 'Sebastian', 'Kurfuerst');
		$user_rl = new \F3\Fluid\ViewHelpers\Fixtures\UserDomainClass(3, 'Robert', 'Lemke');

		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(array(
			'options' => array(
				$user_is,
				$user_sk,
				$user_rl
			),
			'value' => array($user_rl, $user_is),
			'optionValueField' => 'id',
			'optionLabelField' => 'lastName',
			'name' => 'myName',
			'multiple' => 'multiple'
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function selectWithoutFurtherConfigurationOnDomainObjectsUsesUuidForValueAndLabel() {
		$mockPersistenceManager = $this->getMock('F3\FLOW3\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue('fakeUUID'));
		$this->viewHelper->injectPersistenceManager($mockPersistenceManager);

		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value="fakeUUID">fakeUUID</option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$user = new \F3\Fluid\ViewHelpers\Fixtures\UserDomainClass(1, 'Ingmar', 'Schlecht');

		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(array(
			'options' => array(
				$user
			),
			'name' => 'myName'
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function selectWithoutFurtherConfigurationOnDomainObjectsUsesToStringForLabelIfAvailable() {
		$mockPersistenceManager = $this->getMock('F3\FLOW3\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue('fakeUUID'));
		$this->viewHelper->injectPersistenceManager($mockPersistenceManager);

		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$this->viewHelper->expects($this->once())->method('registerFieldNameForFormTokenGeneration')->with('myName');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value="fakeUUID">toStringResult</option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$user = $this->getMock('F3\Fluid\ViewHelpers\Fixtures\UserDomainClass', array('__toString'), array(1, 'Ingmar', 'Schlecht'));
		$user->expects($this->atLeastOnce())->method('__toString')->will($this->returnValue('toStringResult'));

		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(array(
			'options' => array(
				$user
			),
			'name' => 'myName'
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @expectedException \F3\Fluid\Core\ViewHelper\Exception
	 */
	public function selectOnDomainObjectsThrowsExceptionIfNoValueCanBeFound() {
		$mockPersistenceBackend = $this->getMock('F3\FLOW3\Persistence\Backend\BackendInterface');
		$mockPersistenceBackend->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue(NULL));

		$mockPersistenceManager = $this->getMock('F3\FLOW3\Persistence\PersistenceManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getBackend')->will($this->returnValue($mockPersistenceBackend));
		$this->viewHelper->injectPersistenceManager($mockPersistenceManager);

		$mockTagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$user = new \F3\Fluid\ViewHelpers\Fixtures\UserDomainClass(1, 'Ingmar', 'Schlecht');

		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(array(
			'options' => array(
				$user
			),
			'name' => 'myName'
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderCallsSetErrorClassAttribute() {
		$arguments = new \F3\Fluid\Core\ViewHelper\Arguments(array(
			'options' => array()
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->expects($this->once())->method('setErrorClassAttribute');
		$this->viewHelper->render();
	}
}

?>