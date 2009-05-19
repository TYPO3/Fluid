<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Form;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package
 * @subpackage
 * @version $Id$
 */

/**
 * Test for the Abstract Form view helper
 *
 * @package
 * @subpackage
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class AbstractFormViewHelperTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function ifAnAttributeValueIsAnObjectMaintainedByThePersistenceManagerItIsConvertedToAUUID() {
		$mockPersistenceBackend = $this->getMock('F3\FLOW3\Persistence\BackendInterface');
		$mockPersistenceBackend->expects($this->any())->method('getUUIDByObject')->will($this->returnValue('6f487e40-4483-11de-8a39-0800200c9a66'));

		$mockPersistenceManager = $this->getMock('F3\FLOW3\Persistence\ManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getBackend')->will($this->returnValue($mockPersistenceBackend));

		$object = new \stdclass;

		$formViewHelper = $this->getMock($this->buildAccessibleProxy('F3\Fluid\ViewHelpers\Form\AbstractFormViewHelper'), array('dummy'), array(), '', FALSE);
		$formViewHelper->injectPersistenceManager($mockPersistenceManager);
		$formViewHelper->_set('arguments', array('name' => 'foo', 'value' => $object, 'property' => NULL));
		$formViewHelper->expects($this->any())->method('isObjectAccessorMode')->will($this->returnValue(FALSE));

		$this->assertSame('foo[__identity]', $formViewHelper->_call('getName'));
		$this->assertSame('6f487e40-4483-11de-8a39-0800200c9a66', $formViewHelper->_call('getValue'));
	}

}

?>