<?php
namespace TYPO3\Fluid\Tests\Functional\Core;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Uri;

/**
 * Testcase for Parser, checking whether basic parsing features work
 */
class ParserIntegrationTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var boolean
	 */
	protected $testableHttpEnabled = TRUE;

	/**
	 * @return array
	 */
	public function exampleTemplates() {
		return array(
			'simple object access works' => array(
				'source' => 'Hallo {name}',
				'variables' => array('name' => 'Welt'),
				'Hallo Welt'
			),
			'arrays as ViewHelper arguments work' => array(
				'source' => '<f:for each="{0: 10, 1: 20}" as="number">{number}</f:for>',
				'variables' => array(),
				'1020'
			),
			'arrays outside ViewHelper arguments are not parsed' => array(
				'source' => '{0: 10, 1: 20}',
				'variables' => array(),
				'{0: 10, 1: 20}'
			)
		);
	}

	/**
	 * @test
	 * @dataProvider exampleTemplates
	 */
	public function templateIsEvaluatedCorrectly($source, $variables, $expected) {
		$request = Request::create(new Uri('http://localhost'));
		$actionRequest = $request->createActionRequest();

		$standaloneView = new \TYPO3\Fluid\Tests\Functional\View\Fixtures\View\StandaloneView($actionRequest, uniqid());
		$standaloneView->assignMultiple($variables);
		$standaloneView->setTemplateSource($source);

		$actual = $standaloneView->render();
		$this->assertSame($expected, $actual);
	}
}
?>