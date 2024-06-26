<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;

final class RootNodeTest extends TestCase
{
    #[Test]
    public function testEvaluateCallsEvaluateChildNodes(): void
    {
        $subject = $this->getMockBuilder(RootNode::class)->onlyMethods(['evaluateChildNodes'])->getMock();
        $subject->expects(self::once())->method('evaluateChildNodes');
        $subject->evaluate(new RenderingContext());
    }
}
