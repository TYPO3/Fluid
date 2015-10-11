<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Conditions;

use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class BasicConditionsTest
 */
class BasicConditionsTest extends BaseFunctionalTestCase {

	/**
	 * @return array
	 */
	public function getTemplateCodeFixturesAndExpectations() {
		return array(
			'1 == 1' => array(
				'<f:if condition="1 == 1" then="yes" else="no" />',
				array(),
				array('yes'),
				array('no')
			),
			'1 != 2' => array(
				'<f:if condition="1 != 2" then="yes" else="no" />',
				array(),
				array('yes'),
				array('no')
			),
			'1 == 2' => array(
				'<f:if condition="1 < 2" then="yes" else="no" />',
				array(),
				array('yes'),
				array('no')
			),
			'1 === 1' => array(
				'<f:if condition="1 === 1" then="yes" else="no" />',
				array(),
				array('yes'),
				array('no')
			),
			'\'foo\' == 0' => array(
				'<f:if condition="\'foo\' == 0" then="yes" else="no" />',
				array(),
				array('yes'),
				array('no')
			),
			'1.1 >= \'foo\'' => array(
				'<f:if condition="1.1 >= \'foo\'" then="yes" else="no" />',
				array(),
				array('yes'),
				array('no')
			),
			'\'String containing word "false" in text\'' => array(
				'<f:if condition="\'String containing word \"false\" in text\'" then="yes" else="no" />',
				array(),
				array('yes'),
				array('no')
			),
			'\'  FALSE  \'' => array(
				'<f:if condition="\'  FALSE  \'" then="yes" else="no" />',
				array(),
				array('yes'),
				array('no')
			),
			'\'foo\' > 0' => array(
				'<f:if condition="\'foo\' > 0" then="yes" else="no" />',
				array(),
				array('no'),
				array('yes')
			),
		);
	}

}
