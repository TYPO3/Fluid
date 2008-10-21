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

require_once(__DIR__ . '/Fixtures/F3_Beer3_SomeEmptyClass.php');

/**
 * Testcase for ObjectAccessor
 *
 * @package
 * @subpackage Tests
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class ObjectAccessorNodeTest extends F3::Testing::BaseTestCase {
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectAccessorWorksWithStrings() {
		$expected = 'ExpectedString';
		
		$objectAccessorNode = new F3::Beer3::ObjectAccessorNode("exampleObject");
		$context = new F3::Beer3::Context(array('exampleObject' => $expected));

		$actual = $objectAccessorNode->evaluate($context);
		$this->assertEquals($expected, $actual, 'ObjectAccessorNode did not work for string input.');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectAccessorWorksWithNestedObjects() {
		$exampleObject = new F3::Beer3::SomeEmptyClass("Hallo");
		
		$objectAccessorNode = new F3::Beer3::ObjectAccessorNode("exampleObject.subproperty");
		$context = new F3::Beer3::Context(array('exampleObject' => $exampleObject));
		
		$actual = $objectAccessorNode->evaluate($context);
		$this->assertEquals("Hallo", $actual, 'ObjectAccessorNode did not work for calling getters.');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectAccessorWorksWithDirectProperties() {
		$expected = 'This is a test';
		$exampleObject = new F3::Beer3::SomeEmptyClass("Hallo");
		$exampleObject->publicVariable = $expected;
		$objectAccessorNode = new F3::Beer3::ObjectAccessorNode("exampleObject.publicVariable");
		$context = new F3::Beer3::Context(array('exampleObject' => $exampleObject));
		
		$actual = $objectAccessorNode->evaluate($context);
		$this->assertEquals($expected, $actual, 'ObjectAccessorNode did not work for calling getters.');
	}
	
	/**
	 * @test
	 * @expectedException F3::Beer3::RuntimeException
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectAccessorThrowsErrorIfPropertyDoesNotExist() {
		$expected = 'This is a test';
		$exampleObject = new F3::Beer3::SomeEmptyClass("Hallo");
		$exampleObject->publicVariable = $expected;
		$objectAccessorNode = new F3::Beer3::ObjectAccessorNode("exampleObject.publicVariableNotExisting");
		$context = new F3::Beer3::Context(array('exampleObject' => $exampleObject));
		
		$actual = $objectAccessorNode->evaluate($context);
		$this->assertEquals($expected, $actual, 'ObjectAccessorNode did not work for calling getters.');
	}
}



?>