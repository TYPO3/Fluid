<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\ViewHelpers\ElseViewHelper;

/**
 * Testcase for ElseViewHelper
 */
class ElseViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function testInitializeArgumentsRegistersExpectedArguments()
    {
        $instance = $this->getMock(ElseViewHelper::class, ['registerArgument']);
        $instance->expects(self::exactly(1))->method('registerArgument')->with('if', 'boolean', self::anything());
        $instance->initializeArguments();
    }

    /**
     * @test
     */
    public function renderRendersChildren()
    {
        $viewHelper = $this->getMock(ElseViewHelper::class, ['renderChildren']);

        $viewHelper->expects(self::once())->method('renderChildren')->willReturn('foo');
        $actualResult = $viewHelper->render();
        self::assertEquals('foo', $actualResult);
    }

    /**
     * @test
     */
    public function testCompilesToEmptyString()
    {
        $viewHelper = new ElseViewHelper();
        $node = $this->getMock(ViewHelperNode::class, [], [], '', false);
        $compiler = $this->getMock(TemplateCompiler::class);
        $init = '';
        $result = $viewHelper->compile('', '', $init, $node, $compiler);
        self::assertEmpty($init);
        self::assertEquals('\'\'', $result);
    }
}
