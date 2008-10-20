<?php
declare(ENCODING = 'utf-8');
namespace F3::Beer3;

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
 * @package Beer3
 * @subpackage Tests
 * @version $Id:$
 */
/**
 * Testcase for Context
 *
 * @package Beer3
 * @subpackage Tests
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class ContextTest extends F3::Testing::BaseTestCase {

	/**
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setUp() {
		$this->context = new F3::Beer3::Context();
	}
	/**
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function tearDown() {
		unset($this->context);
	}
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function addedObjectsCanBeRetrievedAgain() {
		$object = "StringObject";
		$this->context->add("variable", $object);
		$this->assertSame($this->context->get('variable'), $object, 'The retrieved object from the context is not the same as the stored object.');
	}
	
	/**
	 * @test
	 * @expectedException F3::Beer3::Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function duplicateIdentifiersThrowException() {
		$this->context->add('variable', 'string1');
		$this->context->add('variable', 'string2');
	}
	
	/**
	 * @test
	 * @expectedException F3::Beer3::Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function gettingNonexistentValueThrowsException() {
		$this->context->get('nonexistent');
	}
	
	/**
	 * @test
	 * @expectedException F3::Beer3::Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function deletingNonexistentValueThrowsException() {
		$this->context->remove('nonexistent');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function deleteReallyDeletesObjects() {
		$this->context->add('variable', 'string1');
		$this->context->remove('variable');
		try {
			$this->context->get('variable');
		} catch (F3::Beer3::Exception $e) {}
	}
}



?>