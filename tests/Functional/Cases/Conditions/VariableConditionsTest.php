<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Conditions;

use TYPO3Fluid\Fluid\Tests\Functional\BaseConditionalFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;

/**
 * Class VariableConditionsTest
 */
class VariableConditionsTest extends BaseConditionalFunctionalTestCase
{

    /**
     * @return array
     */
    public function getTemplateCodeFixturesAndExpectations()
    {
        $user1 = new UserWithToString('foobar');
        $user2 = new UserWithToString('foobar');
        $someObject = new \stdClass();
        $someObject->someString = 'bar';
        $someObject->someInt = 1337;
        $someObject->someFloat = 13.37;
        $someObject->someBoolean = true;
        $someArray = [
            'foo' => 'bar'
        ];

        $emptyCountable = new \SplObjectStorage();
        return [
            // simple assignments
            ['{test}', true, ['test' => 1]],
            ['{test}', true, ['test' => '\'  FALSE  \'']],
            ['{test}', true, ['test' => '\'  0  \'']],
            ['{test}', false, ['test' => 0]],
            ['1 == {test}', true, ['test' => 1]],
            ['1 != {test}', true, ['test' => 2]],
            ['{test1} == {test2}', true, ['test1' => 'abc', 'test2' => 'abc']],
            ['{test1} === {test2}', true, ['test1' => 'abc', 'test2' => 'abc']],
            ['{test1} === {test2}', false, ['test1' => 1, 'test2' => true]],
            ['{test1} == {test2}', true, ['test1' => 1, 'test2' => true]],

            // conditions with objects
            ['{user1} == {user1}', true, ['user1' => $user1]],
            ['{user1} === {user1}',true, ['user1' => $user1]],
            ['{user1} == {user2}', false, ['user1' => $user1, 'user2' => $user2]],
            ['{user1} === {user2}', false, ['user1' => $user1, 'user2' => $user2]],

            // condition with object properties
            ['{someObject.someString} == \'bar\'', true, ['someObject' => $someObject]],
            ['{someObject.someString} === \'bar\'', true, ['someObject' => $someObject]],

            ['{someObject.someInt} == \'1337\'', true, ['someObject' => $someObject]],
            ['{someObject.someInt} === \'1337\'', false, ['someObject' => $someObject]],
            ['{someObject.someInt} === 1337', true, ['someObject' => $someObject]],

            ['{someObject.someFloat} == \'13.37\'', true, ['someObject' => $someObject]],
            ['{someObject.someFloat} === \'13.37\'', false, ['someObject' => $someObject]],
            ['{someObject.someFloat} === 13.37', true, ['someObject' => $someObject]],

            ['{someObject.someBoolean} == 1', true, ['someObject' => $someObject]],
            ['{someObject.someBoolean} === 1', false, ['someObject' => $someObject]],
            ['{someObject.someBoolean} == TRUE', true, ['someObject' => $someObject]],
            ['{someObject.someBoolean} === TRUE', true, ['someObject' => $someObject]],

            // array conditions
            ['{someArray} == {foo: \'bar\'}', true, ['someArray' => $someArray]],
            ['{someArray} === {foo: \'bar\'}', true, ['someArray' => $someArray]],
            ['{someArray.foo} == \'bar\'', true, ['someArray' => $someArray]],
            ['({someArray.foo} == \'bar\') && (TRUE || 0)', true, ['someArray' => $someArray]],
            ['({foo.someArray.foo} == \'bar\') && (TRUE || 0)', true, ['foo' => ['someArray' => $someArray]]],

            // inline viewHelpers
            ['(TRUE && ({f:if(condition: \'TRUE\', then: \'1\')} == 1)', true],
            ['(TRUE && ({f:if(condition: \'TRUE\', then: \'1\')} == 0)', false],

            //conditions with countable objects
            ['{emptyCountable}', false, ['emptyCountable' => $emptyCountable]],
            ['FALSE || FALSE', false, ['emptyCountable' => $emptyCountable]],
            ['{emptyCountable} || FALSE', false, ['emptyCountable' => $emptyCountable]],
            ['FALSE || {emptyCountable}', false, ['emptyCountable' => $emptyCountable]],
            // inline if-viewhelper condition with countable objects
            ['{f:if(condition: \'{emptyCountable} || FALSE\', else: \'1\')} == 1)', true, ['emptyCountable' => $emptyCountable]]
        ];
    }
}
