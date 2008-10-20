<?php
declare(ENCODING = 'utf-8');
namespace F3::Beer3;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package Beer3
 * @subpackage Tests
 * @version $Id:$
 */
/**
 * Testcase for TemplateParser
 *
 * @package Beer3
 * @subpackage Tests
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TemplateParserTest extends F3::Testing::BaseTestCase {

	/**
	 * @var F3::Beer3::TemplateParser
	 */
	protected $templateParser;

	/**
	 * Sets up this test case
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setUp() {
		$this->templateParser = $this->componentFactory->getComponent('F3::Beer3::TemplateParser');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.or>
	 * @expectedException F3::Beer3::Exception
	 */
	public function parseThrowsExceptionWhenStringArgumentMissing() {
		$this->templateParser->parse(123);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function parseExtractsNamespacesCorrectly() {
		$this->templateParser->parse("{namespace f3=F3::Beer3::Blablubb} \{namespace f4=F7::Rocks} {namespace f4=F3::Rocks}");
		$expected = array(
			'f3' => 'F3::Beer3::Blablubb',
			'f4' => 'F3::Rocks'
		);
		$this->assertEquals($this->templateParser->getNamespaces(), $expected, 'Namespaces do not match.');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @expectedException F3::Beer3::Exception
	 */
	public function parseThrowsExceptionIfNamespaceIsRedeclared() {
		$this->templateParser->parse("{namespace f3=F3::Beer3::Blablubb} {namespace f3=F3::Rocks}");
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture01ReturnsCorrectObjectTree() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture01.html', FILE_TEXT);
		
		$rootNode = new F3::Beer3::RootNode();
		$rootNode->addSubNode(new F3::Beer3::TextNode("\na"));
		$dynamicNode = new F3::Beer3::DynamicNode('F3::Beer3::ViewHelpers', 'base', array());
		$rootNode->addSubNode($dynamicNode);
		$rootNode->addSubNode(new F3::Beer3::Textnode('b'));
		
		$expected = $rootNode;
		$actual = $this->templateParser->parse($templateSource);
		$this->assertEquals($expected, $actual, 'Fixture 01 was not parsed correctly.');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture02ReturnsCorrectObjectTree() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture02.html', FILE_TEXT);
		
		$rootNode = new F3::Beer3::RootNode();
		$rootNode->addSubNode(new F3::Beer3::TextNode("\n"));
		$dynamicNode = new F3::Beer3::DynamicNode('F3::Beer3::ViewHelpers', 'base', array());
		$dynamicNode->addSubNode(new F3::Beer3::TextNode("\nHallo\n"));
		$rootNode->addSubNode($dynamicNode);
		$dynamicNode = new F3::Beer3::DynamicNode('F3::Beer3::ViewHelpers', 'base', array());
		$dynamicNode->addSubNode(new F3::Beer3::TextNode("Second"));
		$rootNode->addSubNode($dynamicNode);
		
		$expected = $rootNode;
		$actual = $this->templateParser->parse($templateSource);
		$this->assertEquals($expected, $actual, 'Fixture 02 was not parsed correctly.');
	}
	
	/**
	 * @test
	 * @expectedException F3::Beer3::Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture03ThrowsExceptionBecauseWrongTagNesting() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture03.html', FILE_TEXT);
		$this->templateParser->parse($templateSource);
	}
	
	/**
	 * @test
	 * @expectedException F3::Beer3::Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture04ThrowsExceptionBecauseClosingATagWhichWasNeverOpened() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture04.html', FILE_TEXT);
		$this->templateParser->parse($templateSource);
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture05ReturnsCorrectObjectTree() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture05.html', FILE_TEXT);
		
		$rootNode = new F3::Beer3::RootNode();
		$rootNode->addSubNode(new F3::Beer3::TextNode("\na"));
		$dynamicNode = new F3::Beer3::ObjectAccessorNode('posts.bla.Testing3');
		$rootNode->addSubNode($dynamicNode);
		$rootNode->addSubNode(new F3::Beer3::Textnode('b'));
		
		$expected = $rootNode;
		$actual = $this->templateParser->parse($templateSource);
		$this->assertEquals($expected, $actual, 'Fixture 05 was not parsed correctly.');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture06ReturnsCorrectObjectTree() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture06.html', FILE_TEXT);
		
		$rootNode = new F3::Beer3::RootNode();
		$rootNode->addSubNode(new F3::Beer3::TextNode("\n"));
		$arguments = array(
			'each' => new F3::Beer3::RootNode(),
			'as' => new F3::Beer3::RootNode()
		);
		$arguments['each']->addSubNode(new F3::Beer3::ObjectAccessorNode('posts'));
		$arguments['as']->addSubNode(new F3::Beer3::TextNode('post'));
		$dynamicNode = new F3::Beer3::DynamicNode('F3::Beer3::ViewHelpers', 'for', $arguments);
		$rootNode->addSubNode($dynamicNode);
		$dynamicNode->addSubNode(new F3::Beer3::TextNode("\n"));
		$dynamicNode->addSubNode(new F3::Beer3::ObjectAccessorNode('post'));
		$dynamicNode->addSubNode(new F3::Beer3::TextNode("\n"));

		$expected = $rootNode;
		$actual = $this->templateParser->parse($templateSource);
		$this->assertEquals($expected, $actual, 'Fixture 06 was not parsed correctly.');
	}
}



?>