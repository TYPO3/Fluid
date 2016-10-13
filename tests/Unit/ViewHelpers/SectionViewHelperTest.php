<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\SectionViewHelper;

/**
 * Testcase for SectionViewHelper
 *
 */
class SectionViewHelperTest extends UnitTestCase
{

    /**
     * @test
     */
    public function sectionIsAddedToParseVariableContainer()
    {
        $section = new SectionViewHelper();

        $viewHelperNodeMock = $this->getMock(ViewHelperNode::class, [], [], '', false);
        $viewHelperArguments = [
            'name' => new TextNode('sectionName')
        ];

        $variableContainer = new StandardVariableProvider();

        $section->postParseEvent($viewHelperNodeMock, $viewHelperArguments, $variableContainer);

        $this->assertTrue($variableContainer->exists('1457379500_sections'), 'Sections array was not created, albeit it should.');
        $sections = $variableContainer->get('1457379500_sections');
        $this->assertEquals($sections['sectionName'], $viewHelperNodeMock, 'ViewHelperNode for section was not stored.');
    }

    /**
     * @test
     */
    public function testCompileReturnsEmptyString()
    {
        $section = new SectionViewHelper();
        $init = '';
        $viewHelperNodeMock = $this->getMock(ViewHelperNode::class, [], [], '', false);
        $result = $section->compile('fake', 'fake', $init, $viewHelperNodeMock, new TemplateCompiler());
        $this->assertEquals('\'\'', $result);
    }
}
