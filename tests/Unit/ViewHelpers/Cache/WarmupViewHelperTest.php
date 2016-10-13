<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Cache;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingChildrenException;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3Fluid\Fluid\ViewHelpers\Cache\WarmupViewHelper;

/**
 * Testcase for StaticViewHelper
 */
class WarmupViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function testCompileThrowsStopCompilingChildrenException()
    {
        $subject = new WarmupViewHelper();
        $subject->initializeArguments();
        $subject->setRenderChildrenClosure(function () {
            return 'test';
        });
        $subject->setArguments(['variables' => ['foo' => 'bar']]);
        $renderingContext = new RenderingContextFixture();
        $renderingContext->setVariableProvider(new StandardVariableProvider());
        $subject->setRenderingContext($renderingContext);
        $viewHelperNode = $this->getMock(ViewHelperNode::class, ['dummy'], [], '', false);
        $initializationCode = '';
        $this->setExpectedException(StopCompilingChildrenException::class);
        $subject->compile(
            'argumentName',
            'closureName',
            $initializationCode,
            $viewHelperNode,
            new TemplateCompiler()
        );
    }

    /**
     * @test
     */
    public function testRenderReturnsContentDirectlyOutsideWarmupMode()
    {
        $compiler = $this->getMock(TemplateCompiler::class, ['isWarmupMode']);
        $compiler->expects($this->once())->method('isWarmupMode')->willReturn(false);
        $renderingContext = $this->getMock(RenderingContextFixture::class, ['setVariableProvider']);
        $renderingContext->expects($this->never())->method('setVariableProvider');
        $renderingContext->setTemplateCompiler($compiler);
        $subject = new WarmupViewHelper();
        $subject->setRenderChildrenClosure(function () {
            return 'test';
        });
        $subject->setRenderingContext($renderingContext);
        $this->assertEquals('test', $subject->render());
    }

    /**
     * @test
     */
    public function testRenderOverlaysAndRestoresVariableProviderAndReturnsContentInsideWarmupMode()
    {
        $compiler = $this->getMock(TemplateCompiler::class, ['isWarmupMode']);
        $compiler->expects($this->once())->method('isWarmupMode')->willReturn(true);
        $renderingContext = $this->getMock(RenderingContextFixture::class, ['setVariableProvider']);
        $renderingContext->expects($this->exactly(2))->method('setVariableProvider');
        $renderingContext->setTemplateCompiler($compiler);
        $subject = new WarmupViewHelper();
        $subject->setArguments(['variables' => ['foo' => 'bar']]);
        $subject->setRenderChildrenClosure(function () {
            return 'test';
        });
        $subject->setRenderingContext($renderingContext);
        $this->assertEquals('test', $subject->render());
    }
}
