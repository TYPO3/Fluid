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
    public function testInitializeArgumentsRegistersExpectedArguments()
    {
        $instance = $this->getMock(LayoutViewHelper::class, ['registerArgument']);
        $instance->expects($this->at(0))->method('registerArgument')->with('name', 'string', $this->anything());
        $instance->initializeArguments();
    }

    /**
     * @test
     */
    public function testRenderReturnsNull()
    {
        $instance = new LayoutViewHelper();
        $result = $instance->render();
        $this->assertNull($result);
    }

    /**
     * @test
     * @dataProvider getPostParseEventTestValues
     * @param arary $arguments
     * @param string $expectedLayoutName
     */
    public function testPostParseEvent(array $arguments, $expectedLayoutName)
    {
        $instance = new LayoutViewHelper();
        $variableContainer = new StandardVariableProvider();
        $node = new ViewHelperNode(new RenderingContextFixture(), 'f', 'layout', $arguments, new ParsingState());
        $result = LayoutViewHelper::postParseEvent($node, $arguments, $variableContainer);
        $this->assertNull($result);
        $this->assertEquals($expectedLayoutName, $variableContainer->get('layoutName'));
    }

    /**
     * @return array
     */
    public function getPostParseEventTestValues()
    {
        return [
            [['name' => 'test'], 'test'],
            [[], new TextNode('Default')],
        ];
    }
}
