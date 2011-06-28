<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Uri;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

/**
 * Testcase for the external uri view helper
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ExternalViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * var \TYPO3\Fluid\ViewHelpers\Uri\ExternalViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = new \TYPO3\Fluid\ViewHelpers\Uri\ExternalViewHelper();
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderReturnsSpecifiedUri() {
		$this->viewHelper->initialize();
		$actualResult = $this->viewHelper->render('http://www.some-domain.tld');

		$this->assertEquals('http://www.some-domain.tld', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderAddsHttpPrefixIfSpecifiedUriDoesNotContainScheme() {
		$this->viewHelper->initialize();
		$actualResult = $this->viewHelper->render('www.some-domain.tld');

		$this->assertEquals('http://www.some-domain.tld', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderAddsSpecifiedSchemeIfUriDoesNotContainScheme() {
		$this->viewHelper->initialize();
		$actualResult = $this->viewHelper->render('some-domain.tld', 'ftp');

		$this->assertEquals('ftp://some-domain.tld', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderDoesNotAddEmptyScheme() {
		$this->viewHelper->initialize();
		$actualResult = $this->viewHelper->render('some-domain.tld', '');

		$this->assertEquals('some-domain.tld', $actualResult);
	}
}


?>
