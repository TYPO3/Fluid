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
	 */
	public function fixture01ReturnsCorrectObjectTree() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture01.ts2', FILE_TEXT);
		//$expected = new F3::Beer3::RootNode();
	}
}



?>