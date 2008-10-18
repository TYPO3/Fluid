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
 * @subpackage Test
 * @version $Id:$
 */
/**
 * Testcase for Regular expressions in parser
 *
 * @package
 * @subpackage Tests
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Test extends F3::Testing::BaseTestCase {

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function testSCAN_PATTERN_NAMESPACEDECLARATION() {
		$pattern = F3::Beer3::TemplateParser::SCAN_PATTERN_NAMESPACEDECLARATION;
		$this->assertEquals(preg_match($pattern, '{namespace f3=F3::Bla::blubb}'), 1, 'The SCAN_PATTERN_NAMESPACEDECLARATION pattern did not match a namespace declaration (1).');
		$this->assertEquals(preg_match($pattern, '{namespace f3=F3::Bla::Blubb }'), 1, 'The SCAN_PATTERN_NAMESPACEDECLARATION pattern did not match a namespace declaration (2).');
		$this->assertEquals(preg_match($pattern, '{namespace f3 = F3::Bla3::Blubb }'), 1, 'The SCAN_PATTERN_NAMESPACEDECLARATION pattern did not match a namespace declaration (3).');
		$this->assertEquals(preg_match($pattern, ' \{namespace f3 = F3::Bla3::Blubb }'), 0, 'The SCAN_PATTERN_NAMESPACEDECLARATION pattern did match a namespace declaration even if it was escaped. (1)');
		$this->assertEquals(preg_match($pattern, '\{namespace f3 = F3::Bla3::Blubb }'), 0, 'The SCAN_PATTERN_NAMESPACEDECLARATION pattern did match a namespace declaration even if it was escaped. (2)');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function testSPLIT_PATTERN_DYNAMICTAGS() {
		$pattern = $this->insertNamespaceIntoRegularExpression(F3::Beer3::TemplateParser::SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS, array('f3', 't3'));

		$source = '<html><head> <f3:blablubb> {testing}</f4:blz> </t3:hi:jo>';
		$expected = array('<html><head> ', '<f3:blablubb>', ' {testing}</f4:blz> ', '</t3:hi:jo>');
		$this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_DYNAMICTAGS pattern did not split the input string correctly.');

		$source = 'hi<f3:testing attribute="Hallo{yep}" nested:attribute="jup" />ja';
		$expected = array('hi', '<f3:testing attribute="Hallo{yep}" nested:attribute="jup" />', 'ja');
		$this->assertEquals(preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY), $expected, 'The SPLIT_PATTERN_DYNAMICTAGS pattern did not split the input string correctly if complex tags are inside.');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function testSCAN_PATTERN_DYNAMICTAG() {
		$pattern = $this->insertNamespaceIntoRegularExpression(F3::Beer3::TemplateParser::SCAN_PATTERN_TEMPLATE_DYNAMICTAG, array('f3'));
		$source = '<f3:crop attribute="Hallo">';
		$expected = array (
			0 => '<f3:crop attribute="Hallo">',
			'NamespaceIdentifier' => 'f3',
			1 => 'f3',
			'MethodIdentifier' => 'crop',
			2 => 'crop',
			'Attributes' => ' attribute="Hallo"',
			3 => ' attribute="Hallo"',
			'Selfclosing' => '',
			4 => ''
		);
		preg_match($pattern, $source, $matches);
		$this->assertEquals($expected, $matches, 'The SCAN_PATTERN_DYNAMICTAG does not match correctly.');
		
		$source = '<f3:crop attribute="Hallo"/>';
		$expected = array (
			0 => '<f3:crop attribute="Hallo"/>',
			'NamespaceIdentifier' => 'f3',
			1 => 'f3',
			'MethodIdentifier' => 'crop',
			2 => 'crop',
			'Attributes' => ' attribute="Hallo"',
			3 => ' attribute="Hallo"',
			'Selfclosing' => '/',
			4 => '/'
		);
		preg_match($pattern, $source, $matches);
		$this->assertEquals($expected, $matches, 'The SCAN_PATTERN_DYNAMICTAG does not match correctly with self-closing tags.');
		
		$source = '<f3:link:uriTo complex:attribute="Hallo"  />';
		$expected = array (
			0 => '<f3:link:uriTo complex:attribute="Hallo"  />',
			'NamespaceIdentifier' => 'f3',
			1 => 'f3',
			'MethodIdentifier' => 'link:uriTo',
			2 => 'link:uriTo',
			'Attributes' => ' complex:attribute="Hallo"  ',
			3 => ' complex:attribute="Hallo"  ',
			'Selfclosing' => '/',
			4 => '/'
		);
		preg_match($pattern, $source, $matches);
		$this->assertEquals($expected, $matches, 'The SCAN_PATTERN_DYNAMICTAG does not match correctly with complex attributes.');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function testSCAN_PATTERN_CLOSINGDYNAMICTAG() {
		$pattern = $this->insertNamespaceIntoRegularExpression(F3::Beer3::TemplateParser::SCAN_PATTERN_TEMPLATE_CLOSINGDYNAMICTAG, array('f3'));
		$this->assertEquals(preg_match($pattern, '</f3:bla>'), 1, 'The SCAN_PATTERN_CLOSINGDYNAMICTAG does not match a tag it should match.');
		$this->assertEquals(preg_match($pattern, '</f3:bla    >'), 1, 'The SCAN_PATTERN_CLOSINGDYNAMICTAG does not match a tag (with spaces included) it should match.');
		$this->assertEquals(preg_match($pattern, '</t3:bla>'), 0, 'The SCAN_PATTERN_CLOSINGDYNAMICTAG does match match a tag it should not match.');
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function testSPLIT_PATTERN_TAGARGUMENTS() {
		$pattern = F3::Beer3::TemplateParser::SPLIT_PATTERN_TAGARGUMENTS;
		$source = ' test="Hallo" argument:post="\'Web" other=\'Single"Quoted\'';
		$this->assertEquals(preg_match($pattern, $source), 3, 'The SPLIT_PATTERN_TAGARGUMENTS does not match correctly.');
	}
	/**
	 * Helper method which replaces NAMESPACE in the regular expression with the real namespace used.
	 * 
	 * @param string $regularExpression The regular expression in which to replace NAMESPACE
	 * @param array $namespace List of namespace identifiers.
	 * @return string working regular expression with NAMESPACE replaced.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function insertNamespaceIntoRegularExpression($regularExpression, $namespace) {
		return str_replace('NAMESPACE', implode('|', $namespace), $regularExpression);
	}
}



?>