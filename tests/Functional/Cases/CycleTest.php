<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases;

use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class CycleTest
 */
class CycleTest extends BaseFunctionalTestCase {

	/**
	 * @return array
	 */
	public function getTemplateCodeFixturesAndExpectations() {
		return array(
			'Cycles values in array' => array(
				'<f:for each="{items}" as="item"><f:cycle values="{cycles}" as="cycled">{cycled}</f:cycle></f:for>',
				array('items' => array(0, 1, 2, 3), 'cycles' => array('a', 'b')),
				array('abab'),
				array('aa', 'bb')
			),
		);
	}

}
