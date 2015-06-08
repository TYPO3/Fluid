<?php
namespace NamelessCoder\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Compiler\TemplateCompiler;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\TextNode;
use NamelessCoder\Fluid\Core\Variables\StandardVariableProvider;
use NamelessCoder\Fluid\Core\ViewHelper\TemplateVariableContainer;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperResolver;
use NamelessCoder\Fluid\Tests\UnitTestCase;
use NamelessCoder\Fluid\ViewHelpers\SectionViewHelper;

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

		$viewHelperNodeMock = $this->getMock('NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array(), array(), '', FALSE);
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
		$viewHelperNodeMock = $this->getMock('NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array(), array(), '', FALSE);
		$result = $section->compile('fake', 'fake', $init, $viewHelperNodeMock, new TemplateCompiler());
		$this->assertEquals('\'\'', $result);
	}

}
