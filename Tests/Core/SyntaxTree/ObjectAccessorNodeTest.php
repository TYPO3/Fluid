<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\SyntaxTree;

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
 * @package Fluid
 * @subpackage Tests
 * @version $Id:$
 */

require_once(__DIR__ . '/../Fixtures/SomeEmptyClass.php');

/**
 * Testcase for ObjectAccessor
 *
 * @package
 * @subpackage Tests
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class ObjectAccessorNodeTest extends \F3\Testing\BaseTestCase {
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectAccessorWorksWithStrings() {
		$expected = 'ExpectedString';
		
		$objectAccessorNode = new \F3\Fluid\Core\SyntaxTree\ObjectAccessorNode("exampleObject");
		$context = new \F3\Fluid\Core\VariableContainer(array('exampleObject' => $expected));

		$actual = $objectAccessorNode->evaluate($context);
		$this->assertEquals($expected, $actual, 'ObjectAccessorNode did not work for string input.');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectAccessorWorksWithNestedObjects() {
		$exampleObject = new \F3\Fluid\SomeEmptyClass("Hallo");
		
		$objectAccessorNode = new \F3\Fluid\Core\SyntaxTree\ObjectAccessorNode("exampleObject.subproperty");
		$context = new \F3\Fluid\Core\VariableContainer(array('exampleObject' => $exampleObject));
		
		$actual = $objectAccessorNode->evaluate($context);
		$this->assertEquals("Hallo", $actual, 'ObjectAccessorNode did not work for calling getters.');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectAccessorWorksWithDirectProperties() {
		$expected = 'This is a test';
		$exampleObject = new \F3\Fluid\SomeEmptyClass("Hallo");
		$exampleObject->publicVariable = $expected;
		$objectAccessorNode = new \F3\Fluid\Core\SyntaxTree\ObjectAccessorNode("exampleObject.publicVariable");
		$context = new \F3\Fluid\Core\VariableContainer(array('exampleObject' => $exampleObject));
		
		$actual = $objectAccessorNode->evaluate($context);
		$this->assertEquals($expected, $actual, 'ObjectAccessorNode did not work for direct properties.');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectAccessorWorksOnAssociativeArrays() {
		$expected = 'My value';
		$exampleArray = array('key' => array('key2' => $expected));
		$objectAccessorNode = new \F3\Fluid\Core\SyntaxTree\ObjectAccessorNode('variable.key.key2');
		$context = new \F3\Fluid\Core\VariableContainer(array('variable' => $exampleArray));
		
		$actual = $objectAccessorNode->evaluate($context);
		$this->assertEquals($expected, $actual, 'ObjectAccessorNode did not traverse associative arrays.');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @expectedException \F3\Fluid\Core\RuntimeException
	 */
	public function objectAccessorThrowsExceptionIfKeyInAssociativeArrayDoesNotExist() {
		$this->markTestIncomplete('Objects accessors fail silently so far. We need some context dependencies here.');
		$expected = 'My value';
		$exampleArray = array('key' => array('key2' => $expected));
		$objectAccessorNode = new \F3\Fluid\Core\SyntaxTree\ObjectAccessorNode('variable.key.key3');
		$context = new \F3\Fluid\Core\VariableContainer(array('variable' => $exampleArray));
		
		$actual = $objectAccessorNode->evaluate($context);
	}
	
	/**
	 * @test
	 * @expectedException \F3\Fluid\Core\RuntimeException
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectAccessorThrowsErrorIfPropertyDoesNotExist() {
		$this->markTestIncomplete('Objects accessors fail silently so far. We need some context dependencies here.');
		
		$expected = 'This is a test';
		$exampleObject = new \F3\Fluid\SomeEmptyClass("Hallo");
		$exampleObject->publicVariable = $expected;
		$objectAccessorNode = new \F3\Fluid\Core\SyntaxTree\ObjectAccessorNode("exampleObject.publicVariableNotExisting");
		$context = new \F3\Fluid\Core\VariableContainer(array('exampleObject' => $exampleObject));
		
		$actual = $objectAccessorNode->evaluate($context);
	}
	
	
}



?>
