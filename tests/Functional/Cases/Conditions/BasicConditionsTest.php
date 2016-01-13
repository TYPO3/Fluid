<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Conditions;

use TYPO3Fluid\Fluid\Tests\Functional\BaseConditionalFunctionalTestCase;

/**
 * Class BasicConditionsTest
 */
class BasicConditionsTest extends BaseConditionalFunctionalTestCase {

	/**
	 * @return array
	 */
	public function getTemplateCodeFixturesAndExpectations() {
		return array(
			array('1 == 1', TRUE),
			array('1 != 2', TRUE),
			array('1 == 2', FALSE),
			array('1 === 1', TRUE),
			array('\'foo\' == 0', TRUE),
			array('1.1 >= \'foo\'', TRUE),
			array('\'String containing word \"false\" in text\'', TRUE),
			array('\'  FALSE  \'', TRUE),
			array('\'foo\' > 0', FALSE),
			array('FALSE', FALSE),
			array('(FALSE || (FALSE || 1)', TRUE),
			array('(FALSE || (FALSE || 1)', TRUE),
			array('(FALSE || (FALSE || 1)', TRUE),

			// integers
			array('13 == \'13\'', TRUE),
			array('13 === \'13\'', FALSE),

			// floats
			array('13.37 == \'13.37\'', TRUE),
			array('13.37 === \'13.37\'', FALSE),

			// groups
			array('(1 && (\'foo\' == \'foo\') && (TRUE || 1)) && 0 != 1', TRUE),
			array('(1 && (\'foo\' == \'foo\') && (TRUE || 1)) && 0 != 1 && FALSE', FALSE)
		);
	}

}
