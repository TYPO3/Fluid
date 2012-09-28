<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Parser\Interceptor;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for Interceptor\Resource
 *
 */
class ResourceTest extends \TYPO3\Flow\Tests\UnitTestCase {

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

		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->at(0))->method('get')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode')->will($this->returnValue($mockDummyNode));
		$mockObjectManager->expects($this->at(1))->method('get')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', $originalText1)->will($this->returnValue($mockDummyNode));
		$mockObjectManager->expects($this->at(2))->method('get')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', $path)->will($this->returnValue($mockPathNode));
		$mockObjectManager->expects($this->at(3))->method('get')->with('TYPO3\Fluid\ViewHelpers\Uri\ResourceViewHelper')->will($this->returnValue($mockViewHelper));
		$mockObjectManager->expects($this->at(4))->method('get')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', $mockViewHelper, array('path' => $mockPathNode))->will($this->returnValue($mockDummyNode));
		$mockObjectManager->expects($this->at(5))->method('get')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', $originalText3)->will($this->returnValue($mockDummyNode));

		$interceptor = new \TYPO3\Fluid\Core\Parser\Interceptor\Resource();
		$interceptor->injectObjectManager($mockObjectManager);
		$interceptor->process($mockTextNode, \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_TEXT, $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState'));
	}

}

?>