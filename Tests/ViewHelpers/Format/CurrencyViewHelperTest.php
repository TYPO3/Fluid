<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Format;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 */
class CurrencyViewHelperTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperRoundsFloatCorrectly() {
		$viewHelper = new \F3\Fluid\ViewHelpers\Format\CurrencyViewHelper();
		$actualResult = $viewHelper->render(123.456);
		$this->assertEquals('123,46', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperRendersCurrencySign() {
		$viewHelper = new \F3\Fluid\ViewHelpers\Format\CurrencyViewHelper();
		$actualResult = $viewHelper->render(123, 'foo');
		$this->assertEquals('123,00 foo', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperRespectsDecimalSeparator() {
		$viewHelper = new \F3\Fluid\ViewHelpers\Format\CurrencyViewHelper();
		$actualResult = $viewHelper->render(12345, '', '|');
		$this->assertEquals('12.345|00', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperRespectsThousandsSeparator() {
		$viewHelper = new \F3\Fluid\ViewHelpers\Format\CurrencyViewHelper();
		$actualResult = $viewHelper->render(12345, '', ',', '|');
		$this->assertEquals('12|345,00', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperRendersNullValues() {
		$viewHelper = new \F3\Fluid\ViewHelpers\Format\CurrencyViewHelper();
		$actualResult = $viewHelper->render(NULL);
		$this->assertEquals('0,00', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperRendersNegativeAmounts() {
		$viewHelper = new \F3\Fluid\ViewHelpers\Format\CurrencyViewHelper();
		$actualResult = $viewHelper->render(-123.456);
		$this->assertEquals('-123,46', $actualResult);
	}
}
?>
