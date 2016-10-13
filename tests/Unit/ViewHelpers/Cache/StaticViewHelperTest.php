<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Cache;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingChildrenException;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3Fluid\Fluid\ViewHelpers\Cache\StaticViewHelper;

/**
 * Testcase for StaticViewHelper
 */
class StaticViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function testRenderCallsRenderChildren()
    {
        $subject = $this->getMockBuilder(StaticViewHelper::class)->setMethods(['renderChildren'])->getMock();
        $subject->expects($this->once())->method('renderChildren')->willReturn('test');
        $this->assertEquals('test', $subject->render());
    }

    /**
     * @test
     */
    public function testCompile()
    {
        $subject = new StaticViewHelper();
        $subject->setRenderingContext(new RenderingContextFixture());
        $node = $this->getMockBuilder(ViewHelperNode::class)
            ->setMethods(['evaluateChildNodes'])
            ->disableOriginalConstructor()
            ->getMock();
        $node->expects($this->once())->method('evaluateChildNodes');
        $compiler = new TemplateCompiler();
        $this->setExpectedException(StopCompilingChildrenException::class);
        $code = '';
        $subject->compile('test', 'test', $code, $node, $compiler);
    }
}
