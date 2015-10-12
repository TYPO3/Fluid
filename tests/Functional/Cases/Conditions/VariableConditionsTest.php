<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Conditions;

use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class VariableConditionsTest
 */
class VariableConditionsTest extends BaseFunctionalTestCase {

	/**
	 * @return array
	 */
	public function getTemplateCodeFixturesAndExpectations() {
		return array(
			'"{test}" (test=1)' => array(
				'<f:if condition="{test}" then="yes" else="no" />',
				array('test' => 1),
				array('yes'),
				array('no')
			),
			'"{test}" (test="\'  FALSE  \'")' => array(
				'<f:if condition="{test}" then="yes" else="no" />',
				array('test' => '\'  FALSE  \''),
				array('yes'),
				array('no')
			),
			'"{test}" (test="\'  0  \'")' => array(
				'<f:if condition="{test}" then="yes" else="no" />',
				array('test' => '\'  0  \''),
				array('yes'),
				array('no')
			),
			'"{test}" (test=0)' => array(
				'<f:if condition="{test}" then="yes" else="no" />',
				array('test' => 0),
				array('no'),
				array('yes')
			),
			'"1 == {test}" (test=1)' => array(
				'<f:if condition="1 == {test}" then="yes" else="no" />',
				array('test' => 1),
				array('yes'),
				array('no')
			),
			'"1 != {test}" (test=2)' => array(
				'<f:if condition="1 != {test}" then="yes" else="no" />',
				array('test' => 2),
				array('yes'),
				array('no')
			),
			'"{test1} == {test2}" (test1=abc, test2=abc)' => array(
				'<f:if condition="{test1} == {test2}" then="yes" else="no" />',
				array('test1' => 'abc', 'test2' => 'abc'),
				array('yes'),
				array('no')
			),
			'"{test1} === {test2}" (test1=abc, test2=abc)' => array(
				'<f:if condition="{test1} === {test2}" then="yes" else="no" />',
				array('test1' => 'abc', 'test2' => 'abc'),
				array('yes'),
				array('no')
			),
			'"{test1} === {test2}" (test1=1, test2=TRUE)' => array(
				'<f:if condition="{test1} === {test2}" then="yes" else="no" />',
				array('test1' => 1, 'test2' => TRUE),
				array('no'),
				array('yes')
			),
			'"{test1} == {test2}" (test1=1, test2=TRUE)' => array(
				'<f:if condition="{test1} == {test2}" then="yes" else="no" />',
				array('test1' => 1, 'test2' => TRUE),
				array('yes'),
				array('no')
			),
		);
	}

}
