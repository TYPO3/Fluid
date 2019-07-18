<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Conditions;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class VariableConditionsTest
 */
class TernaryConditionsTest extends BaseFunctionalTestCase
{

    /**
     * @return array
     */
    public function getTemplateCodeFixturesAndExpectations(): array
    {
        $someObject = new \stdClass();
        $someObject->someString = 'bar';
        $someObject->someInt = 1337;
        $someObject->someFloat = 13.37;
        $someObject->someBoolean = true;

        return [
            [
                '{foo ? \'yes\' : \'no\'}',
                ['foo' => 'bar'],
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
                '{foo ? 1 : 2}',
                ['foo' => true],
                [1],
                [2]
            ],
            [
                '{foo ? 1 : 2}',
                ['foo' => true],
                [1],
                [2]
            ],
            [
                '{foo ?: \'bar\'}',
                ['foo' => false],
                ['bar'],
                ['foo']
            ],
        ];
    }
}
