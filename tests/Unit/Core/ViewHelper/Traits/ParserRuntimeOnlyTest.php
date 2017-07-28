<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Traits;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

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
        $this->assertNull($result);
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
        $this->assertNull($result);
    }
}
