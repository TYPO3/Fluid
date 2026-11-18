<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Rendering;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Stringable;
use Throwable;
use TYPO3Fluid\Fluid\Core\ErrorHandler\StandardErrorHandler;
use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Parser\TemplateLocation;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as ViewHelperException;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class RuntimeExceptionsTest extends AbstractFunctionalTestCase
{
    public static function renderingExceptionContextDataProvider(): iterable
    {
        yield 'template' => ['RuntimeException', 'Templates/RuntimeException.html', 'value'];
        yield 'nested partial' => ['RuntimeExceptionPartial', 'Partials/RuntimeExceptionInner.html', 'value'];
        yield 'layout' => ['RuntimeExceptionLayout', 'Layouts/RuntimeExceptionLayout.html', 'value'];
        yield 'template section' => ['RuntimeExceptionSection', 'Templates/RuntimeExceptionSection.html', 'value'];
        yield 'partial section' => ['RuntimeExceptionPartialSection', 'Partials/RuntimeExceptionSection.html', 'value'];
        yield 'dynamic layout name' => ['RuntimeExceptionDynamicLayout', 'Templates/RuntimeExceptionDynamicLayout.html', 'layoutName'];
        yield 'compound dynamic layout name' => ['RuntimeExceptionDynamicLayoutCompound', 'Templates/RuntimeExceptionDynamicLayoutCompound.html', 'layoutName'];
        yield 'ViewHelper argument' => ['RuntimeExceptionArgument', 'Templates/RuntimeExceptionArgument.html', 'value'];
    }

    #[Test]
    #[DataProvider('renderingExceptionContextDataProvider')]
    public function invalidStringConversionsContainTheInnermostTemplatePath(string $template, string $expectedPath, string $variable): void
    {
        foreach ([false, true] as $compiled) {
            $view = $this->createView($compiled);
            foreach ([new \stdClass(), []] as $value) {
                $view->assignMultiple(['value' => 'ok', 'layoutName' => 'RuntimeExceptionLayout']);
                $view->assign($variable, $value);
                $error = $this->renderError($view, $template);
                self::assertInstanceOf(ParserException::class, $error);
                self::assertSame(is_array($value) ? 1698750868 : 1273753083, $error->getCode());
                self::assertSame(
                    (is_array($value) ? 'Cannot cast an array to string.' : 'Cannot cast object of type "stdClass" to string.')
                    . ' -> ' . __DIR__ . '/../../Fixtures/' . $expectedPath,
                    $error->getMessage(),
                );
                // Reuse must return to the root context after failures in nested scopes.
                $view->assignMultiple(['value' => 'ok', 'layoutName' => 'RuntimeExceptionLayout']);
                self::assertStringContainsString('before ok after', $view->render($template));
            }
        }
    }

    #[Test]
    #[DataProvider('renderingExceptionContextDataProvider')]
    public function applicationStringConversionExceptionsArePreserved(string $template, string $expectedPath, string $variable): void
    {
        $original = new \DomainException('Application error.', 1770001395);
        foreach ([false, true] as $compiled) {
            $view = $this->createView($compiled);
            $view->assignMultiple(['value' => 'ok', 'layoutName' => 'RuntimeExceptionLayout']);
            $view->assign($variable, $this->throwingStringable($original));
            self::assertSame($original, $this->renderError($view, $template));
            $view->assignMultiple(['value' => 'ok', 'layoutName' => 'RuntimeExceptionLayout']);
            self::assertStringContainsString('before ok after', $view->render($template));
        }
    }

    public static function applicationExceptionDataProvider(): iterable
    {
        yield 'application exception' => [new \DomainException('Application error.')];
        yield 'existing template location' => [new ParserException('Already located.', 1770001395, null, new TemplateLocation('/original.html', 12, 4))];
        // Native exceptions such as PDOException can have string codes.
        $databaseError = new \RuntimeException('Database error.');
        (new \ReflectionProperty(\Exception::class, 'code'))->setValue($databaseError, 'HY000');
        yield 'string exception code' => [$databaseError];
    }

    #[Test]
    #[DataProvider('applicationExceptionDataProvider')]
    public function applicationExceptionsFromViewHelpersAndStringableObjectsArePreserved(Throwable $original): void
    {
        foreach ([false, true] as $compiled) {
            $view = $this->createView($compiled);
            $view->assign('error', $original);
            self::assertSame($original, $this->renderError($view, 'RuntimeExceptionViewHelper'));
            $view->assign('value', $this->throwingStringable($original));
            self::assertSame($original, $this->renderError($view, 'RuntimeException'));
        }
    }

    #[Test]
    public function viewHelperExceptionsStillUseTheConfiguredErrorHandler(): void
    {
        $original = new ViewHelperException('ViewHelper error.', 1770001395);
        foreach ([false, true] as $compiled) {
            $view = $this->createView($compiled);
            $view->assign('error', $original);
            $error = $this->renderError($view, 'RuntimeExceptionViewHelper');
            self::assertInstanceOf(ViewHelperException::class, $error);
            self::assertSame($original, $error->getPrevious());
            self::assertSame($original->getCode(), $error->getCode());
            $path = __DIR__ . '/../../Fixtures/Templates/RuntimeExceptionViewHelper.html';
            self::assertSame('ViewHelper error. -> ' . $path, $error->getMessage());

            $handler = $this->getMockBuilder(StandardErrorHandler::class)->onlyMethods(['handleViewHelperError'])->getMock();
            $handler->expects(self::once())->method('handleViewHelperError')->with(self::identicalTo($original), $path)->willReturn('handled');
            $view->getRenderingContext()->setErrorHandler($handler);
            self::assertSame('handled', trim($view->render('RuntimeExceptionViewHelper')));
        }
    }

    #[Test]
    public function inlineTemplatesKeepConversionAndApplicationExceptionsSeparate(): void
    {
        $original = new \DomainException('Application error.');
        foreach ([false, true] as $compiled) {
            $view = $this->createView($compiled);
            $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('before {value} after');
            $view->assign('value', new \stdClass());
            $error = $this->renderError($view);
            self::assertInstanceOf(ParserException::class, $error);
            self::assertSame('Cannot cast object of type "stdClass" to string.', $error->getMessage());
            self::assertSame(1273753083, $error->getCode());
            $view->assign('value', $this->throwingStringable($original));
            self::assertSame($original, $this->renderError($view));
            $view->assign('value', 'ok');
            self::assertSame('before ok after', $view->render());
        }
    }

    public static function nonStringValueDataProvider(): iterable
    {
        yield 'object' => [new \stdClass()];
        yield 'array' => [['value']];
        yield 'integer' => [42];
        yield 'float' => [1.5];
        yield 'boolean' => [false];
        yield 'null' => [null];
    }

    #[Test]
    #[DataProvider('nonStringValueDataProvider')]
    public function singleNodeTemplatesPreserveNonStringValues(mixed $value): void
    {
        foreach ([false, true] as $compiled) {
            $view = $this->createView($compiled);
            $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('{value}');
            $view->assign('value', $value);
            self::assertSame($value, $view->render());
        }
    }

    private function createView(bool $compiled): TemplateView
    {
        $view = new TemplateView();
        $context = $view->getRenderingContext();
        if ($compiled) {
            $context->setCache(self::$cache);
        }
        $context->getTemplatePaths()->setTemplateRootPaths([__DIR__ . '/../../Fixtures/Templates/']);
        $context->getTemplatePaths()->setPartialRootPaths([__DIR__ . '/../../Fixtures/Partials/']);
        $context->getTemplatePaths()->setLayoutRootPaths([__DIR__ . '/../../Fixtures/Layouts/']);
        $context->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $context->setTemplateParser(new class ($compiled, 'runtime_' . hash('xxh3', self::$cachePath . (int)$compiled)) extends TemplateParser {
            public function __construct(private readonly bool $compiled, private readonly string $prefix) {}

            public function getOrParseAndStoreTemplate(string $templateIdentifier, \Closure $templateSourceClosure, ?string $originalTemplatePath = null): ParsedTemplateInterface
            {
                // Isolate every test and mode from compiled classes already loaded by PHP.
                $templateIdentifier = $this->prefix . '_' . $templateIdentifier;
                $template = parent::getOrParseAndStoreTemplate($templateIdentifier, $templateSourceClosure, $originalTemplatePath);
                if ($this->compiled) {
                    // Parsing stores generated PHP; load it before the first render, including nested templates.
                    $template = $this->renderingContext->getTemplateCompiler()->get($templateIdentifier);
                }
                Assert::assertSame($this->compiled, $template->isCompiled(), $originalTemplatePath ?? 'inline template');
                return $template;
            }
        });
        return $view;
    }

    private function renderError(TemplateView $view, ?string $template = null): Throwable
    {
        try {
            $view->render($template);
        } catch (Throwable $error) {
            return $error;
        }
        self::fail('Rendering must throw an exception.');
    }

    private function throwingStringable(Throwable $error): Stringable
    {
        return new class ($error) implements Stringable {
            public function __construct(private readonly Throwable $error) {}

            public function __toString(): string
            {
                throw $this->error;
            }
        };
    }

    #[Test]
    public function originalTemplatePathIsConsistent(): void
    {
        $assertContains = [
            '|Layout before: ' . __DIR__ . '/../../Fixtures/Layouts/OriginalTemplatePathLayout.html|',
            '|Layout after: ' . __DIR__ . '/../../Fixtures/Layouts/OriginalTemplatePathLayout.html|',
            '|Template before: ' . __DIR__ . '/../../Fixtures/Templates/OriginalTemplatePath.html|',
            '|Template after: ' . __DIR__ . '/../../Fixtures/Templates/OriginalTemplatePath.html|',
            '|Template inline: ' . __DIR__ . '/../../Fixtures/Templates/OriginalTemplatePath.html|',
            '|Partial before: ' . __DIR__ . '/../../Fixtures/Partials/OriginalTemplatePathPartial.html|',
            '|Partial after: ' . __DIR__ . '/../../Fixtures/Partials/OriginalTemplatePathPartial.html|',
            '|Partial nested: ' . __DIR__ . '/../../Fixtures/Partials/OriginalTemplatePathPartialNested.html|',
        ];

        foreach ([false, true] as $compiled) {
            $view = $this->createView($compiled);
            $result = $view->render('OriginalTemplatePath');
            foreach ($assertContains as $contains) {
                self::assertStringContainsString($contains, $result);
            }
        }
    }
}
