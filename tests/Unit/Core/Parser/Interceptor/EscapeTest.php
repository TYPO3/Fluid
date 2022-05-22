<?php

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\Interceptor;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Interceptor\Escape;
use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for Interceptor\Escape
 */
class EscapeTest extends UnitTestCase
{

    /**
     * @var Escape|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $escapeInterceptor;

    /**
     * @var AbstractViewHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $mockViewHelper;

    /**
     * @var ViewHelperNode|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $mockNode;

    /**
     * @var ParsingState|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $mockParsingState;

    public function setUp(): void
    {
        $this->escapeInterceptor = $this->getAccessibleMock(Escape::class, ['dummy']);
        $this->mockViewHelper = $this->getMockBuilder(AbstractViewHelper::class)->disableOriginalConstructor()->getMock();
        $this->mockNode = $this->getMockBuilder(ViewHelperNode::class)->disableOriginalConstructor()->getMock();
        $this->mockParsingState = $this->getMockBuilder(ParsingState::class)
            ->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
    }

    /**
     * @test
     */
    public function processDoesNotDisableEscapingInterceptorByDefault()
    {
        $interceptorPosition = InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER;
        $this->mockViewHelper->expects(self::once())->method('isChildrenEscapingEnabled')->willReturn(true);
        $this->mockNode->expects(self::once())->method('getUninitializedViewHelper')->willReturn($this->mockViewHelper);

        self::assertTrue($this->escapeInterceptor->_get('childrenEscapingEnabled'));
        $this->escapeInterceptor->process($this->mockNode, $interceptorPosition, $this->mockParsingState);
        self::assertTrue($this->escapeInterceptor->_get('childrenEscapingEnabled'));
    }

    /**
     * @test
     */
    public function processDisablesEscapingInterceptorIfViewHelperDisablesIt()
    {
        $interceptorPosition = InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER;
        $this->mockViewHelper->expects(self::once())->method('isChildrenEscapingEnabled')->willReturn(false);
        $this->mockNode->expects(self::once())->method('getUninitializedViewHelper')->willReturn($this->mockViewHelper);

        self::assertTrue($this->escapeInterceptor->_get('childrenEscapingEnabled'));
        $this->escapeInterceptor->process($this->mockNode, $interceptorPosition, $this->mockParsingState);
        self::assertFalse($this->escapeInterceptor->_get('childrenEscapingEnabled'));
    }

    /**
     * @test
     */
    public function processReenablesEscapingInterceptorOnClosingViewHelperTagIfItWasDisabledBefore()
    {
        $interceptorPosition = InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;
        $this->mockViewHelper->expects(self::any())->method('isOutputEscapingEnabled')->willReturn(false);
        $this->mockNode->expects(self::any())->method('getUninitializedViewHelper')->willReturn($this->mockViewHelper);

        $this->escapeInterceptor->_set('childrenEscapingEnabled', false);
        $this->escapeInterceptor->_set('viewHelperNodesWhichDisableTheInterceptor', [$this->mockNode]);

        $this->escapeInterceptor->process($this->mockNode, $interceptorPosition, $this->mockParsingState);
        self::assertTrue($this->escapeInterceptor->_get('childrenEscapingEnabled'));
    }

    /**
     * @test
     */
    public function processWrapsCurrentViewHelperInEscapeNode()
    {
        $interceptorPosition = InterceptorInterface::INTERCEPT_OBJECTACCESSOR;
        $mockNode = $this->getMock(ObjectAccessorNode::class, [], [], '', false);
        $actualResult = $this->escapeInterceptor->process($mockNode, $interceptorPosition, $this->mockParsingState);
        self::assertInstanceOf(EscapingNode::class, $actualResult);
    }
}
