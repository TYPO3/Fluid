<?php
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
use TYPO3Fluid\Fluid\ViewHelpers\LayoutViewHelper;

/**
 * Testcase for LayoutViewHelper
 */
class LayoutViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function testInitializeArgumentsRegistersExpectedArguments(): void
    {
        $instance = $this->getMock(LayoutViewHelper::class, ['registerArgument']);
        $instance->expects($this->at(0))->method('registerArgument')->with('name', 'string', $this->anything());
        $instance->initializeArguments();
    }

    public function testPostParseEvent(): void
    {
        $nameNode = new TextNode('Default');
        $variableContainer = new StandardVariableProvider();
        $node = new ViewHelperNode(new RenderingContextFixture(), 'f', 'layout', ['name' => $nameNode], new ParsingState());
        $result = LayoutViewHelper::postParseEvent($node, ['name' => $nameNode], $variableContainer);
        $this->assertNull($result);
        $this->assertEquals($nameNode, $variableContainer->get('layoutName'));
    }
}
