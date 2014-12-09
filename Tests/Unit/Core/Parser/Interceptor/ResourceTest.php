<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Parser\Interceptor;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
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

	/**
	 * Return source parts and expected results.
	 *
	 * @return array
	 * @see supportedUrlsAreDetected
	 */
	public function supportedUrls() {
		return array(
			array( // mostly harmless
				'<link rel="stylesheet" type="text/css" href="',
				'../../../Public/Backend/Styles/Login.css',
				'">',
				'Backend/Styles/Login.css',
				'Acme.Demo'
			),
			array( // refer to another package
				'<link rel="stylesheet" type="text/css" href="',
				'../../../../Acme.OtherPackage/Resources/Public/Backend/Styles/FromOtherPackage.css',
				'">',
				'Backend/Styles/FromOtherPackage.css',
				'Acme.OtherPackage'
			),
			array( // refer to another package in different category
				'<link rel="stylesheet" type="text/css" href="',
				'../../../Plugins/Acme.OtherPackage/Resources/Public/Backend/Styles/FromOtherPackage.css',
				'">',
				'Backend/Styles/FromOtherPackage.css',
				'Acme.OtherPackage'
			),
			array( // path with spaces (ugh!)
				'<link rel="stylesheet" type="text/css" href="',
				'../../Public/Backend/Styles/With Spaces.css',
				'">',
				'Backend/Styles/With Spaces.css',
				'Acme.Demo'
			),
			array( // single quote around url and spaces
				'<link rel="stylesheet" type="text/css" href=\'',
				'../Public/Backend/Styles/With Spaces.css',
				'\'>',
				'Backend/Styles/With Spaces.css',
				'Acme.Demo'
			)
		);
	}

	/**
	 * @dataProvider supportedUrls
	 * @test
	 */
	public function supportedUrlsAreDetected($part1, $part2, $part3, $expectedPath, $expectedPackageKey) {
		$mockDummyNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface');
		$mockPathNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface');
		$mockPackageKeyNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface');
		$mockViewHelper = $this->getMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper');

		$originalText = $part1 . $part2 . $part3;
		$mockTextNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', array('evaluateChildNodes'), array($originalText));
		$this->assertEquals($originalText, $mockTextNode->evaluate($this->getMock('TYPO3\Fluid\Core\Rendering\RenderingContextInterface')));

		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->at(0))->method('get')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode')->will($this->returnValue($mockDummyNode));
		$mockObjectManager->expects($this->at(1))->method('get')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', $part1)->will($this->returnValue($mockDummyNode));
		$mockObjectManager->expects($this->at(2))->method('get')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', $expectedPath)->will($this->returnValue($mockPathNode));
		$mockObjectManager->expects($this->at(3))->method('get')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', $expectedPackageKey)->will($this->returnValue($mockPackageKeyNode));
		$mockObjectManager->expects($this->at(4))->method('get')->with('TYPO3\Fluid\ViewHelpers\Uri\ResourceViewHelper')->will($this->returnValue($mockViewHelper));
		$mockObjectManager->expects($this->at(5))->method('get')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', $mockViewHelper, array('path' => $mockPathNode, 'package' => $mockPackageKeyNode))->will($this->returnValue($mockDummyNode));
		$mockObjectManager->expects($this->at(6))->method('get')->with('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', $part3)->will($this->returnValue($mockDummyNode));

		$interceptor = new \TYPO3\Fluid\Core\Parser\Interceptor\Resource();
		$interceptor->injectObjectManager($mockObjectManager);
		$interceptor->setDefaultPackageKey('Acme.Demo');
		$interceptor->process($mockTextNode, \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_TEXT, $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState'));
	}

}
