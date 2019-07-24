<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\Interceptor;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\Core\Parser\Interceptor\Escape;
use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for Interceptor\Escape
 */
class EscapeTest extends UnitTestCase
{

    /**
     * @var Escape|MockObject
     */
    protected $escapeInterceptor;

    /**
     * @var AbstractViewHelper|MockObject
     */
    protected $mockViewHelper;

    public function setUp(): void
    {
        $this->escapeInterceptor = $this->getAccessibleMock(Escape::class, ['dummy']);
        $this->mockViewHelper = $this->getMockBuilder(AbstractViewHelper::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @test
     */
    public function processDoesNotDisableEscapingInterceptorByDefault(): void
    {
        $interceptorPosition = InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER;
        $this->mockViewHelper->expects($this->once())->method('isChildrenEscapingEnabled')->will($this->returnValue(true));

        $this->assertSame(0, $this->escapeInterceptor->_get('viewHelperNodesWhichDisableTheInterceptor'));
        $this->escapeInterceptor->process($this->mockViewHelper, $interceptorPosition);
        $this->assertSame(0, $this->escapeInterceptor->_get('viewHelperNodesWhichDisableTheInterceptor'));
    }

    /**
     * @test
     */
    public function processDisablesEscapingInterceptorIfViewHelperDisablesIt(): void
    {
        $interceptorPosition = InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER;
        $this->mockViewHelper->expects($this->once())->method('isChildrenEscapingEnabled')->will($this->returnValue(false));

        $this->assertSame(0, $this->escapeInterceptor->_get('viewHelperNodesWhichDisableTheInterceptor'));
        $this->escapeInterceptor->process($this->mockViewHelper, $interceptorPosition);
        $this->assertSame(1, $this->escapeInterceptor->_get('viewHelperNodesWhichDisableTheInterceptor'));
    }

    /**
     * @test
     */
    public function processReenablesEscapingInterceptorOnClosingViewHelperTagIfItWasDisabledBefore(): void
    {
        $interceptorPosition = InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER;
        $this->mockViewHelper->expects($this->once())->method('isOutputEscapingEnabled')->will($this->returnValue(false));

        $this->escapeInterceptor->_set('viewHelperNodesWhichDisableTheInterceptor', 1);

        $this->escapeInterceptor->process($this->mockViewHelper, $interceptorPosition);
        $this->assertSame(0, $this->escapeInterceptor->_get('viewHelperNodesWhichDisableTheInterceptor'));
    }

    /**
     * @test
     */
    public function processWrapsCurrentViewHelperInEscapeNode(): void
    {
        $interceptorPosition = InterceptorInterface::INTERCEPT_OBJECTACCESSOR;
        $mockNode = $this->getMock(ObjectAccessorNode::class, [], [], false, false);
        $actualResult = $this->escapeInterceptor->process($mockNode, $interceptorPosition);
        $this->assertInstanceOf(EscapingNode::class, $actualResult);
    }
}
