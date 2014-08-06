<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Uri;
use TYPO3\Fluid\ViewHelpers\BaseViewHelper;
use TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase;

require_once(__DIR__ . '/ViewHelperBaseTestcase.php');

/**
 */
class BaseViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @test
	 */
	public function renderTakesBaseUriFromControllerContext() {
		$baseUri = new Uri('http://typo3.org/');

		$this->request->expects($this->any())->method('getHttpRequest')->will($this->returnValue(Request::create($baseUri)));

		$viewHelper = new BaseViewHelper();
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$expectedResult = '<base href="' . htmlspecialchars($baseUri) . '" />';
		$actualResult = $viewHelper->render();
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function renderEscapesBaseUri() {
		$baseUri = new Uri('<some nasty uri>');

		$this->request->expects($this->any())->method('getHttpRequest')->will($this->returnValue(Request::create($baseUri)));

		$viewHelper = new BaseViewHelper();
		$this->injectDependenciesIntoViewHelper($viewHelper);

		$expectedResult = '<base href="http://' . htmlspecialchars($baseUri) . '/" />';
		$actualResult = $viewHelper->render();
		$this->assertSame($expectedResult, $actualResult);
	}
}
