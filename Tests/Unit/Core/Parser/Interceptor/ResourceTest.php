<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Parser\Interceptor;

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
 * Testcase for Interceptor\Resource
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ResourceTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function resourcesInCssUrlsAreReplacedCorrectly() {
		$mockDummyNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface');
		$mockPathNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface');
		$mockViewHelper = $this->getMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper');

		$originalText1 = '<style type="text/css">
			#loginscreen {
				height: 768px;
				background-image: url(';
		$originalText2 = '../../../../Public/Backend/Media/Images/Login/MockLoginScreen.png';
		$path = 'Backend/Media/Images/Login/MockLoginScreen.png';
		$originalText3 = ')
				background-repeat: no-repeat;
			}';
		$originalText = $originalText1 . $originalText2 . $originalText3;
		$mockTextNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', array('evaluateChildNodes'), array($originalText));
		$this->assertEquals($originalText, $mockTextNode->evaluate($this->getMock('TYPO3\Fluid\Core\Rendering\RenderingContextInterface')));

		$mockObjectManager = $this->getMock('TYPO3\FLOW3\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->at(0))->method('create')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', '')->will($this->returnValue($mockDummyNode));
		$mockObjectManager->expects($this->at(1))->method('create')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', $originalText1)->will($this->returnValue($mockDummyNode));
		$mockObjectManager->expects($this->at(2))->method('create')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', $path)->will($this->returnValue($mockPathNode));
		$mockObjectManager->expects($this->at(3))->method('create')->with('TYPO3\Fluid\ViewHelpers\Uri\ResourceViewHelper')->will($this->returnValue($mockViewHelper));
		$mockObjectManager->expects($this->at(4))->method('create')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', $mockViewHelper, array('path' => $mockPathNode))->will($this->returnValue($mockDummyNode));
		$mockObjectManager->expects($this->at(5))->method('create')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', $originalText3)->will($this->returnValue($mockDummyNode));

		$interceptor = new \TYPO3\Fluid\Core\Parser\Interceptor\Resource();
		$interceptor->injectObjectManager($mockObjectManager);
		$interceptor->process($mockTextNode, \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_TEXT, $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState'));
	}

}

?>