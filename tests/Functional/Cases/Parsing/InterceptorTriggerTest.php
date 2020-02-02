<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Parsing;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper;

/**
 * Class InterceptorTriggerTest
 */
class InterceptorTriggerTest extends UnitTestCase
{
    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    protected function setUp(): void
    {
        $this->renderingContext = new RenderingContext();
    }

    /**
     * @param string $source
     * @dataProvider getInterceptObjectAccessorTestValues
     */
    public function testInterceptObjectAccessor(string $source): void
    {
        $interceptor = $this->getMockBuilder(InterceptorInterface::class)->getMockForAbstractClass();
        $interceptor->expects($this->once())->method('getInterceptionPoints')->willReturn([InterceptorInterface::INTERCEPT_OBJECTACCESSOR]);
        $interceptor->expects($this->once())->method('process')->willReturn(new RootNode());
        $this->renderingContext->getParserConfiguration()->addInterceptor($interceptor);
        $this->renderingContext->getRenderer()->renderSource($source);
    }

    public function getInterceptObjectAccessorTestValues(): \Generator
    {
        // Note: inline pipe/pass of ObjectAccessor to ViewHelper is not intercepted; only the final ViewHelper is intercepted.
        yield 'root level object accessor' => ['{object}'];
        yield 'object accessor as inline ViewHelper argument' => ['{f:format.raw(value: object)}'];
        yield 'object accessor as tag ViewHelper argument' => ['<f:format.raw value="{object}" />'];
        yield 'object accessor inside array as tag ViewHelper argument' => ['<f:format.raw value="{foo: object}" />'];
        yield 'object accessor inside array as inline ViewHelper argument' => ['{f:format.raw(value: {foo: object})}'];
        yield 'object accessor inside tag ViewHelper' => ['<f:format.raw>{object}</f:format.raw>'];
    }

    /**
     * @param string $source
     * @dataProvider getInterceptSelfClosingViewHelperTestValues
     */
    public function testInterceptSelfClosingViewHelper(string $source): void
    {
        $interceptor = $this->getMockBuilder(InterceptorInterface::class)->getMockForAbstractClass();
        $interceptor->expects($this->once())->method('getInterceptionPoints')->willReturn([InterceptorInterface::INTERCEPT_SELFCLOSING_VIEWHELPER]);
        $interceptor->expects($this->once())->method('process')->willReturnArgument(0);
        $this->renderingContext->getParserConfiguration()->addInterceptor($interceptor);
        $this->renderingContext->getRenderer()->renderSource($source);
    }

    public function getInterceptSelfClosingViewHelperTestValues(): \Generator
    {
        yield 'self-closing ViewHelper without arguments' => ['<f:format.raw />'];
        yield 'self-closing ViewHelper with arguments' => ['<f:format.raw value="test" />'];
    }

    /**
     * @param string $source
     * @dataProvider getInterceptOpenAndCloseViewHelperTestValues
     */
    public function testInterceptOpenAndCloseViewHelper(string $source): void
    {
        $interceptor = $this->getMockBuilder(InterceptorInterface::class)->getMockForAbstractClass();
        $interceptor->expects($this->once())->method('getInterceptionPoints')->willReturn([InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER, InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER]);
        $interceptor->expects($this->exactly(2))->method('process')->willReturnArgument(0);
        $this->renderingContext->getParserConfiguration()->addInterceptor($interceptor);
        $this->renderingContext->getRenderer()->renderSource($source);
    }

    public function getInterceptOpenAndCloseViewHelperTestValues(): \Generator
    {
        yield 'open and close ViewHelper without arguments' => ['<f:format.raw>test</f:format.raw>'];
        yield 'open and close ViewHelper with arguments' => ['<f:format.raw value="test">test</f:format.raw>'];
    }

    /**
     * @param string $source
     * @dataProvider getInterceptTextTestValues
     */
    public function testInterceptText(string $source): void
    {
        $interceptor = $this->getMockBuilder(InterceptorInterface::class)->getMockForAbstractClass();
        $interceptor->expects($this->once())->method('getInterceptionPoints')->willReturn([InterceptorInterface::INTERCEPT_TEXT]);
        $interceptor->expects($this->atLeastOnce())->method('process')->willReturnArgument(0);
        $this->renderingContext->getParserConfiguration()->addInterceptor($interceptor);
        $this->renderingContext->getRenderer()->renderSource($source);
    }

    public function getInterceptTextTestValues(): \Generator
    {
        yield 'text outside ViewHelper' => ['some text'];
        yield 'text inside ViewHelper tag' => ['<f:format.raw>inside</f:format.raw>'];
        yield 'text inside ViewHelper argument' => ['<f:format.raw value="inside" />'];
        yield 'text inside ViewHelper array argument' => ['<f:format.raw value="{foo: \'inside\'}" />'];
    }

    /**
     * @param string $source
     * @dataProvider getInterceptExpressionTestValues
     */
    public function testInterceptExpression(string $source): void
    {
        $interceptor = $this->getMockBuilder(InterceptorInterface::class)->getMockForAbstractClass();
        $interceptor->expects($this->once())->method('getInterceptionPoints')->willReturn([InterceptorInterface::INTERCEPT_EXPRESSION]);
        $interceptor->expects($this->once())->method('process')->willReturn(new RootNode());
        $this->renderingContext->getParserConfiguration()->addInterceptor($interceptor);
        $this->renderingContext->getRenderer()->renderSource($source);
    }

    public function getInterceptExpressionTestValues(): \Generator
    {
        // Note: inline pipe/pass of ObjectAccessor to ViewHelper is not intercepted; only the final ViewHelper is intercepted.
        yield 'root level expression' => ['{value + 1}'];
        yield 'expression as inline ViewHelper argument' => ['{f:format.raw(value: "{value + 1}")}'];
        yield 'expression as tag ViewHelper argument' => ['<f:format.raw value="{value + 1}" />'];
        yield 'expression inside array as tag ViewHelper argument' => ['<f:format.raw value="{foo: \'{value + 1}\'}" />'];
        yield 'expression inside array as inline ViewHelper argument' => ['{f:format.raw(value: {foo: \'{value + 1}\'})}'];
        yield 'expression inside tag ViewHelper' => ['<f:format.raw>{value + 1}</f:format.raw>'];
    }
}
