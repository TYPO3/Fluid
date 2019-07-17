<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\SectionViewHelper;

/**
 * Testcase for SectionViewHelper
 */
class SectionViewHelperTest extends UnitTestCase
{

    /**
     * @test
     */
    public function sectionIsAddedToParseVariableContainer(): void
    {
        $section = new SectionViewHelper();

        $viewHelperArguments = [
            'name' => new TextNode('sectionName')
        ];

        $variableContainer = new StandardVariableProvider();
        $context = new RenderingContextFixture();
        $context->setVariableProvider($variableContainer);

        $state = new ParsingState();
        $state->setVariableProvider($variableContainer);

        $section->postParse($viewHelperArguments, $section->prepareArguments(), $state, $context);

        $this->assertTrue($variableContainer->exists('1457379500_sections'), 'Sections array was not created, albeit it should.');
        $sections = $variableContainer->get('1457379500_sections');
        $this->assertEquals($sections['sectionName'], $section, 'ViewHelperNode for section was not stored.');
    }
}
