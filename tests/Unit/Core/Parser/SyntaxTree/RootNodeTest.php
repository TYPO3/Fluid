<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
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
        /** @var RootNode|MockObject $subject */
        $subject = $this->getMock(RootNode::class, ['evaluateChildNodes']);
        $subject->expects($this->once())->method('evaluateChildNodes');
        $subject->evaluate(new RenderingContextFixture());
    }
}
