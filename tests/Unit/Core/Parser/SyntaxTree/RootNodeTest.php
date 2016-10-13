<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Testcase for RootNode
 *
 */
class RootNodeTest extends UnitTestCase
{

    /**
     * @test
     */
    public function testEvaluateCallsEvaluateChildNodes()
    {
        $view = new TemplateView();
        $subject = $this->getMock(RootNode::class, ['evaluateChildNodes']);
        $subject->expects($this->once())->method('evaluateChildNodes');
        $subject->evaluate(new RenderingContext($view));
    }
}
