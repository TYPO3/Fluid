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

/**
 * Testcase for SectionViewHelper
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class SectionViewHelperTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function sectionIsAddedToParseVariableContainer() {
		$section = new \TYPO3\Fluid\ViewHelpers\SectionViewHelper();
		
		$viewHelperNodeMock = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array(), array(), '', FALSE);
		$viewHelperArguments = array(
			'name' => new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('sectionName')
		);
		
		$variableContainer = new \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer();
		
		$section->postParseEvent($viewHelperNodeMock, $viewHelperArguments, $variableContainer);
		
		$this->assertTrue($variableContainer->exists('sections'), 'Sections array was not created, albeit it should.');
		$sections = $variableContainer->get('sections');
		$this->assertEquals($sections['sectionName'], $viewHelperNodeMock, 'ViewHelperNode for section was not stored.');
	}
}



?>
