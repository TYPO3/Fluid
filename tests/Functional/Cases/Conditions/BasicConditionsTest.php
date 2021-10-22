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
            // expected value based on php versions behaviour
            ['\'foo\' == 0', (PHP_VERSION_ID < 80000 ? true : false)],
            // expected value based on php versions behaviour
            ['1.1 >= \'foo\'', (PHP_VERSION_ID < 80000 ? true : false)],
            ['\'String containing word \"false\" in text\'', true],
            ['\'  FALSE  \'', true],
            // expected value based on php versions behaviour
            ['\'foo\' > 0', (PHP_VERSION_ID < 80000 ? false : true)],
            ['FALSE', false],
            ['(FALSE || (FALSE || 1)', true],
            ['(FALSE || (FALSE || 1)', true],
            ['(FALSE || (FALSE || 1)', true],

            ['(FALSE or (FALSE or 1)', true],

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
