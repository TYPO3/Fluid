<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Escaping;

use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class RecursiveSectionRendering
 */
class RecursiveSectionRenderingTest extends BaseFunctionalTestCase {

	/**
	 * Variables array constructed to expect exactly three
	 * recursive renderings followed by a single rendering.
	 *
	 * @var array
	 */
	protected $variables = array(
		'settings' => array(
			'test' => '<strong>Bla</strong>'
		),
		'items' => array(
			array(
				'id' => 1,
				'items' => array(
					array(
						'id' => 2,
						'items' => array(
							array(
								'id' => 3,
								'items' => array()
							)
						)
					)
				)
			),
			array(
				'id' => 4
			)
		)
	);

	/**
	 * @return array
	 */
	public function getTemplateCodeFixturesAndExpectations() {
		return array(
			'Recursive section rendering clones variable storage and restores after loop ends' => array(
				file_get_contents(__DIR__ . '/../../Fixtures/Templates/RecursiveSectionRendering.html'),
				$this->variables,
				array('Item: 1.', 'Item: 2.', 'Item: 3.', 'Item: 4.'),
				array(),
			),
		);
	}

}
