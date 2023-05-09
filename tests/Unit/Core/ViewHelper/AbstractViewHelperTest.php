<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\Fixtures\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Fixtures\RenderMethodFreeDefaultRenderStaticViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Fixtures\RenderMethodFreeViewHelper;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class AbstractViewHelperTest extends UnitTestCase
{
    public static function getFirstElementOfNonEmptyTestValues(): array
    {
        return [
            'plain array' => [['foo', 'bar'], 'foo'],
            'iterator w/o arrayaccess' => [new \IteratorIterator(new \ArrayIterator(['foo', 'bar'])), 'foo'],
            'unsupported value' => ['unsupported value', null]
        ];
    }

    /**
     * @param mixed $input
     * @param mixed $expected
     * @dataProvider getFirstElementOfNonEmptyTestValues
     */
    public function testGetFirstElementOfNonEmpty($input, $expected): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, []);
        self::assertEquals($expected, $viewHelper->_call('getFirstElementOfNonEmpty', $input));
    }

    /**
     * @test
     */
    public function argumentsCanBeRegistered(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, [], [], '', false);

        $name = 'This is a name';
        $description = 'Example desc';
        $type = 'string';
        $isRequired = true;
        $expected = new ArgumentDefinition($name, $type, $description, $isRequired);

        $viewHelper->_call('registerArgument', $name, $type, $description, $isRequired);
        self::assertEquals([$name => $expected], $viewHelper->prepareArguments());
    }

    /**
     * @test
     */
    public function registeringTheSameArgumentNameAgainThrowsException(): void
    {
        $this->expectException(\Exception::class);

        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, [], [], '', false);

        $name = 'shortName';
        $description = 'Example desc';
        $type = 'string';
        $isRequired = true;

        $viewHelper->_call('registerArgument', $name, $type, $description, $isRequired);
        $viewHelper->_call('registerArgument', $name, 'integer', $description, $isRequired);
    }

    /**
     * @test
     */
    public function overrideArgumentOverwritesExistingArgumentDefinition(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, [], [], '', false);

        $name = 'argumentName';
        $description = 'argument description';
        $overriddenDescription = 'overwritten argument description';
        $type = 'string';
        $overriddenType = 'integer';
        $isRequired = true;
        $expected = new ArgumentDefinition($name, $overriddenType, $overriddenDescription, $isRequired);

        $viewHelper->_call('registerArgument', $name, $type, $description, $isRequired);
        $viewHelper->_call('overrideArgument', $name, $overriddenType, $overriddenDescription, $isRequired);
        self::assertEquals($viewHelper->prepareArguments(), [$name => $expected], 'Argument definitions not returned correctly. The original ArgumentDefinition could not be overridden.');
    }

    /**
     * @test
     */
    public function overrideArgumentThrowsExceptionWhenTryingToOverwriteAnNonexistingArgument(): void
    {
        $this->expectException(\Exception::class);
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, [], [], '', false);
        $viewHelper->_call('overrideArgument', 'argumentName', 'string', 'description', true);
    }

    /**
     * @test
     */
    public function prepareArgumentsCallsInitializeArguments(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['initializeArguments'], [], '', false);
        $viewHelper->expects(self::once())->method('initializeArguments');
        $viewHelper->prepareArguments();
    }

    /**
     * @test
     */
    public function validateArgumentsCallsPrepareArguments(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['prepareArguments'], [], '', false);
        $viewHelper->expects(self::once())->method('prepareArguments')->willReturn([]);
        $viewHelper->validateArguments();
    }

    /**
     * @test
     */
    public function validateArgumentsAcceptsAllObjectsImplemtingArrayAccessAsAnArray(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['prepareArguments'], [], '', false);
        $viewHelper->setArguments(['test' => new \ArrayObject()]);
        $viewHelper->expects(self::once())->method('prepareArguments')->willReturn(['test' => new ArgumentDefinition('test', 'array', false, 'documentation')]);
        $viewHelper->validateArguments();
    }

    /**
     * @test
     */
    public function validateArgumentsCallsTheRightValidators(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['prepareArguments'], [], '', false);
        $viewHelper->setArguments(['test' => 'Value of argument']);
        $viewHelper->expects(self::once())->method('prepareArguments')->willReturn([
            'test' => new ArgumentDefinition('test', 'string', false, 'documentation')
        ]);
        $viewHelper->validateArguments();
    }

    /**
     * @test
     */
    public function validateArgumentsCallsTheRightValidatorsAndThrowsExceptionIfValidationIsWrong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['prepareArguments'], [], '', false);
        $viewHelper->setArguments(['test' => 'test']);
        $viewHelper->expects(self::once())->method('prepareArguments')->willReturn([
            'test' => new ArgumentDefinition('test', 'stdClass', false, 'documentation')
        ]);
        $viewHelper->validateArguments();
    }

    /**
     * @test
     */
    public function initializeArgumentsAndRenderCallsTheCorrectSequenceOfMethods(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['validateArguments', 'initialize', 'callRenderMethod']);
        $viewHelper->expects(self::once())->method('validateArguments');
        $viewHelper->expects(self::once())->method('initialize');
        $viewHelper->expects(self::once())->method('callRenderMethod')->willReturn('Output');

        $expectedOutput = 'Output';
        $actualOutput = $viewHelper->initializeArgumentsAndRender();
        self::assertEquals($expectedOutput, $actualOutput);
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

        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['prepareArguments'], [], '', false);

        $viewHelper->setRenderingContext($renderingContext);

        self::assertSame($viewHelper->_get('templateVariableContainer'), $templateVariableContainer);
        self::assertSame($viewHelper->_get('viewHelperVariableContainer'), $viewHelperVariableContainer);
    }

    /**
     * @test
     */
    public function testRenderChildrenCallsRenderChildrenClosureIfSet(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, [], [], '', false);
        $viewHelper->setRenderChildrenClosure(function () {
            return 'foobar';
        });
        $result = $viewHelper->renderChildren();
        self::assertEquals('foobar', $result);
    }

    public static function getValidateArgumentsErrorsTestValues(): array
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
     * @dataProvider getValidateArgumentsErrorsTestValues
     * @param mixed $value
     */
    public function testValidateArgumentsErrors(ArgumentDefinition $argument, $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $viewHelper = $this->getAccessibleMock(
            AbstractViewHelper::class,
            ['hasArgument', 'prepareArguments'],
            [],
            '',
            false
        );
        $viewHelper->expects(self::once())->method('prepareArguments')->willReturn([$argument->getName() => $argument]);
        $viewHelper->expects(self::once())->method('hasArgument')->with($argument->getName())->willReturn(true);
        $viewHelper->setArguments([$argument->getName() => $value]);
        $viewHelper->validateArguments();
    }

    /**
     * @test
     */
    public function testValidateAdditionalArgumentsThrowsExceptionIfNotEmpty(): void
    {
        $this->expectException(Exception::class);
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, [], [], '', false);
        $viewHelper->setRenderingContext(new RenderingContextFixture());
        $viewHelper->validateAdditionalArguments(['foo' => 'bar']);
    }

    /**
     * @test
     */
    public function testCompileReturnsAndAssignsExpectedPhpCode(): void
    {
        $context = new RenderingContext();
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, [], [], '', false);
        $node = new ViewHelperNode($context, 'f', 'comment', [], new ParsingState());
        $init = '';
        $compiler = new TemplateCompiler();
        $result = $viewHelper->compile('foobar', 'baz', $init, $node, $compiler);
        self::assertEmpty($init);
        self::assertEquals(get_class($viewHelper) . '::renderStatic(foobar, baz, $renderingContext)', $result);
    }

    /**
     * @test
     */
    public function testDefaultResetStateMethodDoesNothing(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, [], [], '', false);
        self::assertNull($viewHelper->resetState());
    }

    /**
     * @test
     */
    public function testCallRenderMethodCanRenderViewHelperWithoutRenderMethodAndCallsRenderStatic(): void
    {
        $subject = new RenderMethodFreeViewHelper();
        $method = new \ReflectionMethod($subject, 'callRenderMethod');
        $subject->setRenderingContext(new RenderingContextFixture());
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
        $subject->setRenderingContext(new RenderingContextFixture());
        $method->invoke($subject);
    }
}
