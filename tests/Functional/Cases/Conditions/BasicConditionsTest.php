<?php
namespace NamelessCoder\Fluid\Tests\Functional\Cases\Conditions;

use NamelessCoder\Fluid\Tests\Functional\BaseFunctionalTestCase;

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
		);
	}

}
