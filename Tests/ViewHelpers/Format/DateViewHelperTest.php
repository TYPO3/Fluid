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
class DateViewHelperTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperFormatsDateCorrectly() {
		$viewHelper = new \F3\Fluid\ViewHelpers\Format\DateViewHelper();
		$date = new \DateTime('1980-12-13');
		$actualResult = $viewHelper->render($date);
		$this->assertEquals('1980-12-13', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperRespectsCustomFormat() {
		$viewHelper = new \F3\Fluid\ViewHelpers\Format\DateViewHelper();
		$date = new \DateTime('1980-02-01');
		$actualResult = $viewHelper->render($date, 'd.m.Y');
		$this->assertEquals('01.02.1980', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperReturnsEmptyStringIfNULLIsGiven() {
		$viewHelper = new \F3\Fluid\ViewHelpers\Format\DateViewHelper();
		$actualResult = $viewHelper->render(NULL);
		$this->assertEquals('', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function viewHelperReturnsEmptyStringIfNoDateTimeObjectIsGiven() {
		$viewHelper = new \F3\Fluid\ViewHelpers\Format\DateViewHelper();
		$actualResult = $viewHelper->render('foo');
		$this->assertEquals('', $actualResult);
	}
}
?>
