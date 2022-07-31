<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\ViewHelpers\ThenViewHelper;

/**
 * Testcase for ElseViewHelper
 */
class ThenViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function renderRendersChildren()
    {
        $viewHelper = $this->getMock(ThenViewHelper::class, ['renderChildren']);

        $viewHelper->expects(self::once())->method('renderChildren')->willReturn('foo');
        $actualResult = $viewHelper->render();
        self::assertEquals('foo', $actualResult);
    }

    /**
     * @test
     */
    public function testCompileReturnsEmptyString()
    {
        $section = new ThenViewHelper();
        $init = '';
        $viewHelperNodeMock = $this->getMock(ViewHelperNode::class, [], [], '', false);
        $result = $section->compile('fake', 'fake', $init, $viewHelperNodeMock, new TemplateCompiler());
        self::assertEquals('\'\'', $result);
    }
}
