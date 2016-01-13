<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Conditions;

use TYPO3Fluid\Fluid\Tests\Functional\BaseConditionalFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;

/**
 * Class VariableConditionsTest
 */
class VariableConditionsTest extends BaseConditionalFunctionalTestCase {

	/**
	 * @return array
	 */
	public function getTemplateCodeFixturesAndExpectations() {
		$user1 = new UserWithToString('foobar');
		$user2 = new UserWithToString('foobar');
		$someObject = new \stdClass();
		$someObject->someString = 'bar';
		$someObject->someInt = 1337;
		$someObject->someFloat = 13.37;
		$someObject->someBoolean = TRUE;
		$someArray = array(
			'foo' => 'bar'
		);
		return array(
			// simple assignments
			array('{test}', TRUE, array('test' => 1)),
			array('{test}', TRUE, array('test' => '\'  FALSE  \'')),
			array('{test}', TRUE, array('test' => '\'  0  \'')),
			array('{test}', FALSE, array('test' => 0)),
			array('1 == {test}', TRUE, array('test' => 1)),
			array('1 != {test}', TRUE, array('test' => 2)),
			array('{test1} == {test2}', TRUE, array('test1' => 'abc', 'test2' => 'abc')),
			array('{test1} === {test2}', TRUE, array('test1' => 'abc', 'test2' => 'abc')),
			array('{test1} === {test2}', FALSE, array('test1' => 1, 'test2' => TRUE)),
			array('{test1} == {test2}', TRUE, array('test1' => 1, 'test2' => TRUE)),

			// conditions with objects
			array('{user1} == {user1}', TRUE, array('user1' => $user1)),
			array('{user1} === {user1}',TRUE, array('user1' => $user1)),
			array('{user1} == {user2}', FALSE, array('user1' => $user1, 'user2' => $user2)),
			array('{user1} === {user2}', FALSE, array('user1' => $user1, 'user2' => $user2)),

			// condition with object properties
			array('{someObject.someString} == \'bar\'', TRUE, array('someObject' => $someObject)),
			array('{someObject.someString} === \'bar\'', TRUE, array('someObject' => $someObject)),

			array('{someObject.someInt} == \'1337\'', TRUE, array('someObject' => $someObject)),
			array('{someObject.someInt} === \'1337\'', FALSE, array('someObject' => $someObject)),
			array('{someObject.someInt} === 1337', TRUE, array('someObject' => $someObject)),

			array('{someObject.someFloat} == \'13.37\'', TRUE, array('someObject' => $someObject)),
			array('{someObject.someFloat} === \'13.37\'', FALSE, array('someObject' => $someObject)),
			array('{someObject.someFloat} === 13.37', TRUE, array('someObject' => $someObject)),

			array('{someObject.someBoolean} == 1', TRUE, array('someObject' => $someObject)),
			array('{someObject.someBoolean} === 1', FALSE, array('someObject' => $someObject)),
			array('{someObject.someBoolean} == TRUE', TRUE, array('someObject' => $someObject)),
			array('{someObject.someBoolean} === TRUE', TRUE, array('someObject' => $someObject)),

			// array conditions
			array('{someArray} == {foo: \'bar\'}', TRUE, array('someArray' => $someArray)),
			array('{someArray} === {foo: \'bar\'}', TRUE, array('someArray' => $someArray)),
			array('{someArray.foo} == \'bar\'', TRUE, array('someArray' => $someArray)),
			array('({someArray.foo} == \'bar\') && (TRUE || 0)', TRUE, array('someArray' => $someArray)),
			array('({foo.someArray.foo} == \'bar\') && (TRUE || 0)', TRUE, array('foo' => array('someArray' => $someArray))),

			// inline viewHelpers
			array('(TRUE && ({f:if(condition: \'TRUE\', then: \'1\')} == 1)', TRUE),
			array('(TRUE && ({f:if(condition: \'TRUE\', then: \'1\')} == 0)', FALSE)
		);
	}

}
