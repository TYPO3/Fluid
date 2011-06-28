<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers;

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

require_once(__DIR__ . '/ViewHelperBaseTestcase.php');

/**
 * Test case for the Translate ViewHelper
 */
class TranslateViewHelperTest extends \TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @test
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function viewHelperTranslates() {
		$dummyLocale = new \TYPO3\FLOW3\I18n\Locale('de_DE');

		$mockTranslator = $this->getMock('TYPO3\FLOW3\I18n\Translator');
		$mockTranslator->expects($this->once())->method('translateByOriginalLabel', 'Untranslated Label', 'main', array(), NULL, $dummyLocale)->will($this->returnValue('Translated Label'));

		$viewHelper = $this->getAccessibleMock('TYPO3\Fluid\ViewHelpers\TranslateViewHelper', array('renderChildren'));
		$viewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('Untranslated Label'));
		$viewHelper->injectTranslator($mockTranslator);

		$result = $viewHelper->render(NULL, NULL, array(), 'main', NULL, 'de_DE');
		$this->assertEquals('Translated Label', $result);
	}
}

?>