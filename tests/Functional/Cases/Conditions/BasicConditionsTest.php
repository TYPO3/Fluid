<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Conditions;

use TYPO3Fluid\Fluid\Tests\Functional\BaseConditionalFunctionalTestCase;

/**
 * Class BasicConditionsTest
 */
class BasicConditionsTest extends BaseConditionalFunctionalTestCase
{

    /**
     * @return array
     */
    public function getTemplateCodeFixturesAndExpectations()
    {
        return [
            ['1 == 1', true],
            ['1 != 2', true],
            ['1 == 2', false],
            ['1 === 1', true],
            ['\'foo\' == 0', true],
            ['1.1 >= \'foo\'', true],
            ['\'String containing word \"false\" in text\'', true],
            ['\'  FALSE  \'', true],
            ['\'foo\' > 0', false],
            ['FALSE', false],
            ['(FALSE || (FALSE || 1)', true],
            ['(FALSE || (FALSE || 1)', true],
            ['(FALSE || (FALSE || 1)', true],

            // integers
            ['13 == \'13\'', true],
            ['13 === \'13\'', false],

            // floats
            ['13.37 == \'13.37\'', true],
            ['13.37 === \'13.37\'', false],

            // groups
            ['(1 && (\'foo\' == \'foo\') && (TRUE || 1)) && 0 != 1', true],
            ['(1 && (\'foo\' == \'foo\') && (TRUE || 1)) && 0 != 1 && FALSE', false]
        ];
    }
}
