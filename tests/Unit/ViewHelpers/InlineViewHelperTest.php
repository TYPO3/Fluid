<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\InlineViewHelper;

/**
 * Class InlineViewHelperTest
 */
class InlineViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @test
     */
    public function testInitializeArguments()
    {
        $instance = $this->getMockBuilder(InlineViewHelper::class)->setMethods(['registerArgument'])->getMock();
        $instance->expects($this->at(0))->method('registerArgument')->with('code', 'string', $this->anything());
        $instance->initializeArguments();
    }

    /**
     * @test
     */
    public function testCallsExpectedDelegationMethodFromRenderStatic()
    {
        $contextFixture = new RenderingContextFixture();

        $parsedTemplateMock = $this->getMockBuilder(ParsedTemplateInterface::class)->getMock();
        $parsedTemplateMock->expects($this->once())->method('render')->with($contextFixture)->willReturn('bar');

        $parserMock = $this->getMockBuilder(TemplateParser::class)->setMethods(['parse'])->getMock();
        $parserMock->expects($this->once())->method('parse')->with('foo')->willReturn($parsedTemplateMock);

        $contextFixture->setTemplateParser($parserMock);

        $result = InlineViewHelper::renderStatic([], function() { return 'foo'; }, $contextFixture);
        $this->assertEquals('bar', $result);
    }
}
