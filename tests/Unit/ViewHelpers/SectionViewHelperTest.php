<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3\Fluid\Tests\UnitTestCase;
use TYPO3\Fluid\ViewHelpers\SectionViewHelper;

/**
 * Testcase for SectionViewHelper
 *
 */
class SectionViewHelperTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function sectionIsAddedToParseVariableContainer() {
		$section = new SectionViewHelper();

		$viewHelperNodeMock = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array(), array(), '', FALSE);
		$viewHelperArguments = array(
			'name' => new TextNode('sectionName')
		);

		$variableContainer = new StandardVariableProvider();

		$section->postParseEvent($viewHelperNodeMock, $viewHelperArguments, $variableContainer);

		$this->assertTrue($variableContainer->exists('sections'), 'Sections array was not created, albeit it should.');
		$sections = $variableContainer->get('sections');
		$this->assertEquals($sections['sectionName'], $viewHelperNodeMock, 'ViewHelperNode for section was not stored.');
	}

	/**
	 * @test
	 */
	public function testCompileReturnsEmptyString() {
		$section = new SectionViewHelper();
		$init = '';
		$result = $section->compile('fake', 'fake', $init, new TextNode('test'), new TemplateCompiler(new ViewHelperResolver()));
		$this->assertEquals('\'\'', $result);
	}

}
