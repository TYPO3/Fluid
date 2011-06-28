<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Identity;

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

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Testcase for IdentityViewHelper
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class JsonIdentityViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function renderGetsIdentityForObjectFromPersistenceManager() {
		$mockPersistenceManager = $this->getMock('TYPO3\FLOW3\Persistence\PersistenceManagerInterface');

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Identity\JsonViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->injectPersistenceManager($mockPersistenceManager);

		$object = new \stdClass();

		$mockPersistenceManager->expects($this->atLeastOnce())->method('getIdentifierByObject')->with($object)->will($this->returnValue('6f487e40-4483-11de-8a39-0800200c9a66'));

		$output = $viewHelper->render($object);

		$this->assertEquals('{"__identity":"6f487e40-4483-11de-8a39-0800200c9a66"}', $output, 'Identity is rendered as is');
	}

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function renderOutputsEmptyJsonObjectForNullIdentity() {
		$mockPersistenceManager = $this->getMock('TYPO3\FLOW3\Persistence\PersistenceManagerInterface');

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\Identity\JsonViewHelper', array('dummy'), array(), '', FALSE);
		$this->injectDependenciesIntoViewHelper($viewHelper);
		$viewHelper->injectPersistenceManager($mockPersistenceManager);

		$object = new \stdClass();

		$mockPersistenceManager->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue(NULL));

		$output = $viewHelper->render($object);

		$this->assertEquals('{}', $output, 'NULL Identity is rendered as empty string');
	}
}

?>