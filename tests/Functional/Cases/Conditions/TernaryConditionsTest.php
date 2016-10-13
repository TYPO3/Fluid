<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Conditions;

use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class VariableConditionsTest
 */
class TernaryConditionsTest extends BaseFunctionalTestCase
{

    /**
     * @return array
     */
    public function getTemplateCodeFixturesAndExpectations()
    {
        $someObject = new \stdClass();
        $someObject->someString = 'bar';
        $someObject->someInt = 1337;
        $someObject->someFloat = 13.37;
        $someObject->someBoolean = true;
        $someArray = [
            'foo' => 'bar'
        ];
        return [
            [
                '{true ? \'yes\' : \'no\'}',
                [],
                ['yes'],
                ['no']
            ],
            [
                '{true ? 1 : 2}',
                [],
                [1],
                [2]
            ],
            [
                '{true ? foo : \'bar\'}',
                ['foo' => 'bar'],
                ['bar'],
                ['foo']
            ],
            [
                '{(true) ? \'yes\' : \'no\'}',
                [],
                ['yes'],
                ['no']
            ],
            [
                '{(true || false) ? \'yes\' : \'no\'}',
                [],
                ['yes'],
                ['no']
            ],
            [
                '{(false || false) ? \'yes\' : \'no\'}',
                [],
                ['no'],
                ['yes']
            ],
            [
                '{foo ? \'yes\' : \'no\'}',
                ['foo' => true],
                ['yes'],
                ['no']
            ],
            [
                '{foo ? \'yes\' : \'no\'}',
                ['foo' => false],
                ['no'],
                ['yes']
            ],
            [
                '{!foo ? \'yes\' : \'no\'}',
                ['foo' => false],
                ['yes'],
                ['no']
            ],
            [
                '{(foo || false) ? \'yes\' : \'no\'}',
                ['foo' => true],
                ['yes'],
                ['no']
            ],
            [
                '{(foo || false) ? \'yes\' : \'no\'}',
                ['foo' => false],
                ['no'],
                ['yes']
            ],
            [
                '{(foo.bar || false) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => true]],
                ['yes'],
                ['no']
            ],
            [
                '{(foo.bar && false) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => true]],
                ['no'],
                ['yes']
            ],
            [
                '{(foo.bar > 10) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => 11]],
                ['yes'],
                ['no']
            ],
            [
                '{(foo.bar < 10) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => 11]],
                ['no'],
                ['yes']
            ],
            [
                '{(foo.bar < 10) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => 11]],
                ['no'],
                ['yes']
            ],
            [
                '{(foo.bar % 10) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => 11]],
                ['yes'],
                ['no']
            ],
            [
                '{(foo.bar % 10) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => 10]],
                ['no'],
                ['yes']
            ],
        ];
    }
}
