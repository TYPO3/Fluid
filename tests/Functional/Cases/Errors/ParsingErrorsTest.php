<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Errors;

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class ParsingErrorsTest
 */
class ParsingErrorsTest extends BaseFunctionalTestCase {

	/**
	 * @return array
	 */
	public function getTemplateCodeFixturesAndExpectations() {
		return array(
			'Unclosed ViewHelperNode' => array(
				'<f:section name="Test"></div>',
				array(),
				array(),
				array(),
				Exception::class
			),
			'Missing required argument' => array(
				'<f:section></f:section>',
				array(),
				array(),
				array(),
				Exception::class
			),
			'Uses invalid namespace' => array(
				'<invalid:section></invalid:section>',
				array(),
				array(),
				array(),
				Exception::class
			),
		);
	}

}
