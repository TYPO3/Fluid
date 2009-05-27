<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Exp;

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
 * @version $Id:$
 */
class EqualsViewHelperTest extends \F3\Testing\BaseTestCase {
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function renderReturnsTrueIfArgumentsAreEqual() {
		$viewHelper = new \F3\Fluid\ViewHelpers\Exp\EqualsViewHelper();
		$stdClass1 = new \stdClass();
		$stdClass2 = new \stdClass();
		$this->assertTrue($viewHelper->render($stdClass1, $stdClass1));
		$this->assertFalse($viewHelper->render($stdClass1, $stdClass2));
	}
}
?>