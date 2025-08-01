<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
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

final class AbstractViewHelperTest extends TestCase
{
    public static function getFirstElementOfNonEmptyTestValues(): array
    {
        return [
            'plain array' => [
                ['foo', 'bar'],
                'foo',
            ],
            'iterator w/o arrayaccess' => [
                new \IteratorIterator(new \ArrayIterator(['foo', 'bar'])),
                'foo',
            ],
            'unsupported value' => [
                'unsupported value',
                null,
            ],
        ];
    }

    #[DataProvider('getFirstElementOfNonEmptyTestValues')]
    #[Test]
    #[IgnoreDeprecations]
    public function getFirstElementOfNonEmptyReturnsExpectedValue(mixed $input, ?string $expected): void
    {
        $subject = new class () extends AbstractViewHelper {
            public function render(): string
            {
                return '';
            }
        };
        $method = new \ReflectionMethod($subject, 'getFirstElementOfNonEmpty');
        self::assertEquals($expected, $method->invoke($subject, $input));
    }

    #[Test]
    public function registeringTheSameArgumentNameAgainOverridesArgument(): void
    {
        $subject = new class () extends AbstractViewHelper {
            public function render(): string
            {
                return '';
            }
        };
        $method = new \ReflectionMethod($subject, 'registerArgument');
        $method->invoke($subject, 'someName', 'string', 'desc', true);
        $method->invoke($subject, 'someName', 'integer', 'changed desc', true);
        $expected = [
            'someName' => new ArgumentDefinition('someName', 'integer', 'changed desc', true),
        ];
        self::assertEquals($expected, $subject->prepareArguments());
    }

    #[Test]
    public function setRenderingContextShouldSetInnerVariables(): void
    {
        $templateVariableContainer = $this->createMock(VariableProviderInterface::class);
        $viewHelperVariableContainer = $this->createMock(ViewHelperVariableContainer::class);
        $renderingContext = new RenderingContext();
        $renderingContext->setVariableProvider($templateVariableContainer);
        $renderingContext->setViewHelperVariableContainer($viewHelperVariableContainer);
        $subject = new class () extends AbstractViewHelper {
            public function render(): string
            {
                return '';
            }
        };
        $subject->setRenderingContext($renderingContext);
        $property = new \ReflectionProperty($subject, 'templateVariableContainer');
        self::assertSame($templateVariableContainer, $property->getValue($subject));
        $property = new \ReflectionProperty($subject, 'viewHelperVariableContainer');
        self::assertSame($viewHelperVariableContainer, $property->getValue($subject));
    }

    #[Test]
    public function renderChildrenCallsRenderChildrenClosureIfSet(): void
    {
        $subject = new class () extends AbstractViewHelper {
            public function render(): string
            {
                return '';
            }
        };
        $subject->setRenderChildrenClosure(function () {
            return 'foobar';
        });
        $result = $subject->renderChildren();
        self::assertEquals('foobar', $result);
    }

    #[Test]
    public function validateAdditionalArgumentsThrowsExceptionIfNotEmpty(): void
    {
        $this->expectException(Exception::class);
        $subject = new class () extends AbstractViewHelper {
            public function render(): string
            {
                return '';
            }
        };
        $subject->setRenderingContext(new RenderingContext());
        $subject->validateAdditionalArguments(['foo' => 'bar']);
    }

    #[Test]
    public function testCompileReturnsAndAssignsExpectedPhpCode(): void
    {
        $context = new RenderingContext();
        $node = new ViewHelperNode($context, 'f', 'comment', []);
        $init = '';
        $subject = new class () extends AbstractViewHelper {
            public function render(): string
            {
                return '';
            }
        };
        $result = $subject->compile('foobar', 'baz', $init, $node, new TemplateCompiler());
        self::assertEmpty($init);
        self::assertEquals('$renderingContext->getViewHelperInvoker()->invoke(' . get_class($subject) . '::class, foobar, $renderingContext, baz)', $result);
    }

    #[Test]
    #[IgnoreDeprecations]
    public function testCallRenderMethodCanRenderViewHelperWithoutRenderMethodAndCallsRenderStatic(): void
    {
        $subject = new RenderMethodFreeViewHelper();
        $method = new \ReflectionMethod($subject, 'render');
        $subject->setRenderingContext(new RenderingContext());
        $result = $method->invoke($subject);
        self::assertSame('I was rendered', $result);
    }

    #[Test]
    public function testCallRenderMethodOnViewHelperWithoutRenderMethodWithDefaultRenderStaticMethodThrowsException(): void
    {
        $this->expectException(Exception::class);
        $subject = new RenderMethodFreeDefaultRenderStaticViewHelper();
        $method = new \ReflectionMethod($subject, 'render');
        $subject->setRenderingContext(new RenderingContext());
        $method->invoke($subject);
    }
}
