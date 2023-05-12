<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Fixtures\RenderMethodFreeDefaultRenderStaticViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Fixtures\RenderMethodFreeViewHelper;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class AbstractViewHelperTest extends UnitTestCase
{
    public static function getFirstElementOfNonEmptyTestValues(): array
    {
        return [
            'plain array' => [
                ['foo', 'bar'],
                'foo'
            ],
            'iterator w/o arrayaccess' => [
                new \IteratorIterator(new \ArrayIterator(['foo', 'bar'])),
                'foo'
            ],
            'unsupported value' => [
                'unsupported value',
                null
            ]
        ];
    }

    /**
     * @dataProvider getFirstElementOfNonEmptyTestValues
     * @test
     */
    public function getFirstElementOfNonEmptyReturnsExpectedValue(mixed $input, string|null $expected): void
    {
        $subject = $this->getMockBuilder(AbstractViewHelper::class)->onlyMethods([])->getMock();
        $method = new \ReflectionMethod($subject, 'getFirstElementOfNonEmpty');
        self::assertEquals($expected, $method->invoke($subject, $input));
    }

    /**
     * @test
     */
    public function overrideArgumentOverwritesExistingArgumentDefinition(): void
    {
        $subject = $this->getMockBuilder(AbstractViewHelper::class)->onlyMethods([])->getMock();
        $method1 = new \ReflectionMethod($subject, 'registerArgument');
        $method1->invoke($subject, 'someName', 'string', 'desc', true);
        $method2 = new \ReflectionMethod($subject, 'overrideArgument');
        $method2->invoke($subject, 'someName', 'integer', 'changed desc', true);
        $expected = [
            'someName' => new ArgumentDefinition('someName', 'integer', 'changed desc', true)
        ];
        self::assertEquals($expected, $subject->prepareArguments());
    }

    /**
     * @test
     */
    public function registeringTheSameArgumentNameAgainThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $subject = $this->getMockBuilder(AbstractViewHelper::class)->onlyMethods([])->getMock();
        $method = new \ReflectionMethod($subject, 'registerArgument');
        $method->invoke($subject, 'someName', 'string', 'desc', true);
        $method->invoke($subject, 'someName', 'integer', 'desc', true);
    }

    /**
     * @test
     */
    public function overrideArgumentThrowsExceptionWhenTryingToOverwriteAnNonexistingArgument(): void
    {
        $this->expectException(\Exception::class);
        $subject = $this->getMockBuilder(AbstractViewHelper::class)->onlyMethods([])->getMock();
        $method = new \ReflectionMethod($subject, 'overrideArgument');
        $method->invoke($subject, 'argumentName', 'string', 'description', true);
    }

    /**
     * @test
     */
    public function validateArgumentsAcceptsAllObjectsImplementingArrayAccessAsAnArray(): void
    {
        $subject = $this->getMockBuilder(AbstractViewHelper::class)->onlyMethods(['prepareArguments'])->getMock();
        $subject->setArguments(['test' => new \ArrayObject()]);
        $subject->expects(self::once())->method('prepareArguments')->willReturn(['test' => new ArgumentDefinition('test', 'array', false, 'documentation')]);
        $subject->validateArguments();
    }

    /**
     * @test
     */
    public function setRenderingContextShouldSetInnerVariables(): void
    {
        $templateVariableContainer = $this->createMock(VariableProviderInterface::class);
        $viewHelperVariableContainer = $this->createMock(ViewHelperVariableContainer::class);
        $renderingContext = new RenderingContext();
        $renderingContext->setVariableProvider($templateVariableContainer);
        $renderingContext->setViewHelperVariableContainer($viewHelperVariableContainer);
        $subject = $this->getMockBuilder(AbstractViewHelper::class)->onlyMethods(['prepareArguments'])->getMock();
        $subject->setRenderingContext($renderingContext);
        $property = new \ReflectionProperty($subject, 'templateVariableContainer');
        self::assertSame($templateVariableContainer, $property->getValue($subject));
        $property = new \ReflectionProperty($subject, 'viewHelperVariableContainer');
        self::assertSame($viewHelperVariableContainer, $property->getValue($subject));
    }

    /**
     * @test
     */
    public function renderChildrenCallsRenderChildrenClosureIfSet(): void
    {
        $subject = $this->getMockBuilder(AbstractViewHelper::class)->onlyMethods([])->getMock();
        $subject->setRenderChildrenClosure(function () {
            return 'foobar';
        });
        $result = $subject->renderChildren();
        self::assertEquals('foobar', $result);
    }

    public static function validateArgumentsErrorsDataProvider(): array
    {
        return [
            [new ArgumentDefinition('test', 'boolean', '', true), ['bad']],
            [new ArgumentDefinition('test', 'string', '', true), new \ArrayIterator(['bar'])],
            [new ArgumentDefinition('test', 'DateTime', '', true), new \ArrayIterator(['bar'])],
            [new ArgumentDefinition('test', 'DateTime', '', true), 'test'],
            [new ArgumentDefinition('test', 'integer', '', true), new \ArrayIterator(['bar'])],
            [new ArgumentDefinition('test', 'object', '', true), 'test'],
            [new ArgumentDefinition('test', 'string[]', '', true), [new \DateTime('now'), 'test']]
        ];
    }

    /**
     * @test
     * @dataProvider validateArgumentsErrorsDataProvider
     */
    public function validateArgumentsErrors(ArgumentDefinition $argument, array|string|object $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $subject = $this->getMockBuilder(AbstractViewHelper::class)->onlyMethods(['hasArgument', 'prepareArguments'])->getMock();
        $subject->expects(self::once())->method('prepareArguments')->willReturn([$argument->getName() => $argument]);
        $subject->expects(self::once())->method('hasArgument')->with($argument->getName())->willReturn(true);
        $subject->setArguments([$argument->getName() => $value]);
        $subject->validateArguments();
    }

    /**
     * @test
     */
    public function validateAdditionalArgumentsThrowsExceptionIfNotEmpty(): void
    {
        $this->expectException(Exception::class);
        $subject = $this->getMockBuilder(AbstractViewHelper::class)->onlyMethods([])->getMock();
        $subject->setRenderingContext(new RenderingContext());
        $subject->validateAdditionalArguments(['foo' => 'bar']);
    }

    /**
     * @test
     */
    public function testCompileReturnsAndAssignsExpectedPhpCode(): void
    {
        $context = new RenderingContext();
        $node = new ViewHelperNode($context, 'f', 'comment', []);
        $init = '';
        $subject = $this->getMockBuilder(AbstractViewHelper::class)->onlyMethods([])->getMock();
        $result = $subject->compile('foobar', 'baz', $init, $node, new TemplateCompiler());
        self::assertEmpty($init);
        self::assertEquals(get_class($subject) . '::renderStatic(foobar, baz, $renderingContext)', $result);
    }

    /**
     * @test
     */
    public function testCallRenderMethodCanRenderViewHelperWithoutRenderMethodAndCallsRenderStatic(): void
    {
        $subject = new RenderMethodFreeViewHelper();
        $method = new \ReflectionMethod($subject, 'callRenderMethod');
        $subject->setRenderingContext(new RenderingContext());
        $result = $method->invoke($subject);
        self::assertSame('I was rendered', $result);
    }

    /**
     * @test
     */
    public function testCallRenderMethodOnViewHelperWithoutRenderMethodWithDefaultRenderStaticMethodThrowsException(): void
    {
        $this->expectException(Exception::class);
        $subject = new RenderMethodFreeDefaultRenderStaticViewHelper();
        $method = new \ReflectionMethod($subject, 'callRenderMethod');
        $subject->setRenderingContext(new RenderingContext());
        $method->invoke($subject);
    }
}
