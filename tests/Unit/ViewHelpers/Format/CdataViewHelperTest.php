<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3Fluid\Fluid\ViewHelpers\Format\CdataViewHelper;

/**
 * Test for \TYPO3Fluid\Fluid\ViewHelpers\Format\CdataViewHelper
 */
class CdataViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @param array $arguments
	 * @param string|NULL $tagContent
	 * @param string $expected
	 * @dataProvider getRenderTestValues
	 */
	public function testRender($arguments, $tagContent, $expected) {
		$instance = new CdataViewHelper();
		$instance->initializeArguments();
		$instance->setArguments($arguments);
		$instance->setRenderingContext(new RenderingContextFixture());
		$instance->setRenderChildrenClosure(function() use ($tagContent) { return $tagContent; });
		$this->assertEquals($expected, $instance->render());
	}

	/**
	 * @return array
	 */
	public function getRenderTestValues() {
		return array(
			array(array(), 'test1', '<![CDATA[test1]]>'),
			array(array('value' => 'test2'), NULL, '<![CDATA[test2]]>'),
		);
	}

}
