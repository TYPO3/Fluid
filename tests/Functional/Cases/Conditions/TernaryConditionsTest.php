<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Conditions;

use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;

/**
 * Class VariableConditionsTest
 */
class TernaryConditionsTest extends BaseFunctionalTestCase {

	/**
	 * @return array
	 */
	public function getTemplateCodeFixturesAndExpectations() {
		$someObject = new \stdClass();
		$someObject->someString = 'bar';
		$someObject->someInt = 1337;
		$someObject->someFloat = 13.37;
		$someObject->someBoolean = TRUE;
		$someArray = array(
			'foo' => 'bar'
		);
		return array(
			array(
				'{true ? \'yes\' : \'no\'}',
				array(),
				array('yes'),
				array('no')
			),
			array(
				'{true ? 1 : 2}',
				array(),
				array(1),
				array(2)
			),
			array(
				'{true ? foo : \'bar\'}',
				array('foo' => 'bar'),
				array('bar'),
				array('foo')
			),
			array(
				'{(true) ? \'yes\' : \'no\'}',
				array(),
				array('yes'),
				array('no')
			),
			array(
				'{(true || false) ? \'yes\' : \'no\'}',
				array(),
				array('yes'),
				array('no')
			),
			array(
				'{(false || false) ? \'yes\' : \'no\'}',
				array(),
				array('no'),
				array('yes')
			),
			array(
				'{foo ? \'yes\' : \'no\'}',
				array('foo' => TRUE),
				array('yes'),
				array('no')
			),
			array(
				'{foo ? \'yes\' : \'no\'}',
				array('foo' => FALSE),
				array('no'),
				array('yes')
			),
			array(
				'{!foo ? \'yes\' : \'no\'}',
				array('foo' => FALSE),
				array('yes'),
				array('no')
			),
			array(
				'{(foo || false) ? \'yes\' : \'no\'}',
				array('foo' => TRUE),
				array('yes'),
				array('no')
			),
			array(
				'{(foo || false) ? \'yes\' : \'no\'}',
				array('foo' => FALSE),
				array('no'),
				array('yes')
			),
			array(
				'{(foo.bar || false) ? \'yes\' : \'no\'}',
				array('foo' => array('bar' => true)),
				array('yes'),
				array('no')
			),
			array(
				'{(foo.bar && false) ? \'yes\' : \'no\'}',
				array('foo' => array('bar' => true)),
				array('no'),
				array('yes')
			),
			array(
				'{(foo.bar > 10) ? \'yes\' : \'no\'}',
				array('foo' => array('bar' => 11)),
				array('yes'),
				array('no')
			),
			array(
				'{(foo.bar < 10) ? \'yes\' : \'no\'}',
				array('foo' => array('bar' => 11)),
				array('no'),
				array('yes')
			),
			array(
				'{(foo.bar < 10) ? \'yes\' : \'no\'}',
				array('foo' => array('bar' => 11)),
				array('no'),
				array('yes')
			),
			array(
				'{(foo.bar % 10) ? \'yes\' : \'no\'}',
				array('foo' => array('bar' => 11)),
				array('yes'),
				array('no')
			),
			array(
				'{(foo.bar % 10) ? \'yes\' : \'no\'}',
				array('foo' => array('bar' => 10)),
				array('no'),
				array('yes')
			),
		);
	}

}
