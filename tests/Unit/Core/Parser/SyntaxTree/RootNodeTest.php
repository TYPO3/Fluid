<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for RootNode
 */
class RootNodeTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testEvaluateCallsEvaluateChildNodes(): void
    {
        $subject = $this->getMock(RootNode::class, ['evaluateChildren']);
        $subject->expects($this->once())->method('evaluateChildren');
        $subject->evaluate(new RenderingContextFixture());
    }
}
