<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Traits;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ParserRuntimeOnly;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class ParserRuntimeOnlyTest
 */
class ParserRuntimeOnlyTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testRenderReturnsNull()
    {
        $instance = $this->getMockBuilder(ParserRuntimeOnly::class)->getMockForTrait();
        $result = $instance->render();
        self::assertNull($result);
    }

    /**
     * @test
     */
    public function testCompileReturnsEmptyString()
    {
        $trait = $this->getMockBuilder(ParserRuntimeOnly::class)->getMockForTrait();
        $init = '';
        $viewHelperNodeMock = $this->getMock(ViewHelperNode::class, [], [], '', false);
        $result = $trait->compile('fake', 'fake', $init, $viewHelperNodeMock, new TemplateCompiler());
        self::assertSame('', $result);
    }
}
