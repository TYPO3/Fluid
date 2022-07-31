<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for RootNode
 */
class RootNodeTest extends UnitTestCase
{

    /**
     * @test
     */
    public function testEvaluateCallsEvaluateChildNodes()
    {
        $subject = $this->getMock(RootNode::class, ['evaluateChildNodes']);
        $subject->expects(self::once())->method('evaluateChildNodes');
        $subject->evaluate(new RenderingContext());
    }
}
