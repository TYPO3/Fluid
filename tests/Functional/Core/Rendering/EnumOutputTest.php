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
use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\BasicComponentCollection;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\EnumExample;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\IntBackedEnumExample;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\StringBackedEnumExample;
use TYPO3Fluid\Fluid\View\TemplateView;

final class EnumOutputTest extends AbstractFunctionalTestCase
{
    public static function enumOutputDataProvider(): iterable
    {
        yield 'int-backed' => [IntBackedEnumExample::BAR, '123', '123'];
        yield 'zero' => [IntBackedEnumExample::ZERO, '0', '0'];
        yield 'negative integer' => [IntBackedEnumExample::NEGATIVE, '-1', '-1'];
        yield 'string-backed' => [StringBackedEnumExample::BAR, 'bar value', 'bar value'];
        yield 'empty string' => [StringBackedEnumExample::EMPTY, '', ''];
        yield 'HTML' => [StringBackedEnumExample::HTML, '<b>&"test"</b>', '&lt;b&gt;&amp;&quot;test&quot;&lt;/b&gt;'];
        yield 'Unicode' => [StringBackedEnumExample::UNICODE, 'Grüße', 'Grüße'];
        yield 'unbacked' => [EnumExample::FOO, 'FOO', 'FOO'];
    }

    #[Test]
    #[DataProvider('enumOutputDataProvider')]
    public function enumsAreRenderedAsExpected(\UnitEnum $enum, string $raw, string $escaped): void
    {
        $templates = [
            '{enum}' => $escaped,
            '{enum -> f:format.raw()}' => $enum,
            '{f:constant(name: constantName)}' => $escaped,
            'before {enum} after' => 'before ' . $escaped . ' after',
            'before {enum -> f:format.raw()} after' => 'before ' . $raw . ' after',
            '<f:format.raw>before {enum} after</f:format.raw>' => 'before ' . $raw . ' after',
            '<f:format.raw value="before {enum} after" />' => 'before ' . $raw . ' after',
            '<my:enumOutput value="{enum}" />' => "\n" . $escaped . "\n",
        ];
        foreach ([false, true] as $compiled) {
            foreach ($templates as $source => $expected) {
                $view = $this->createView($source, $enum, $compiled);
                self::assertSame($expected, $view->render(), $source);
            }
        }
    }

    #[Test]
    #[DataProvider('enumOutputDataProvider')]
    public function enumArgumentsAndPropertiesRemainAvailable(\UnitEnum $enum, string $raw, string $escaped): void
    {
        foreach ([false, true] as $compiled) {
            $view = $this->createView('<f:format.raw value="{enum}" />', $enum, $compiled);
            self::assertSame($enum, $view->render());
            $view = $this->createView('<f:format.raw value="{f:constant(name: constantName)}" />', $enum, $compiled);
            self::assertSame($enum, $view->render());
            $view = $this->createView('{enum.name}', $enum, $compiled);
            self::assertSame($enum->name, $view->render());
            if ($enum instanceof \BackedEnum) {
                $view = $this->createView('{enum.value}', $enum, $compiled);
                self::assertSame(is_int($enum->value) ? $enum->value : $escaped, $view->render());
            }
        }
    }

    private function createView(string $source, \UnitEnum $enum, bool $compiled): TemplateView
    {
        $view = new TemplateView();
        $view->assignMultiple(['enum' => $enum, 'constantName' => $enum::class . '::' . $enum->name]);
        $context = $view->getRenderingContext();
        $context->getTemplatePaths()->setTemplateSource($source);
        $context->getViewHelperResolver()->addNamespace('my', BasicComponentCollection::class);
        if ($compiled) {
            $context->setCache(self::$cache);
        }
        $context->setTemplateParser(new class ($compiled, 'enum_' . hash('xxh3', self::$cachePath . (int)$compiled)) extends TemplateParser {
            public function __construct(private readonly bool $compiled, private readonly string $prefix) {}

            public function getOrParseAndStoreTemplate(string $templateIdentifier, \Closure $templateSourceClosure, ?string $originalTemplatePath = null): ParsedTemplateInterface
            {
                // Isolate both modes from compiled classes loaded by other tests.
                $templateIdentifier = $this->prefix . '_' . $templateIdentifier;
                $template = parent::getOrParseAndStoreTemplate($templateIdentifier, $templateSourceClosure, $originalTemplatePath);
                if ($this->compiled) {
                    // Execute generated PHP on the first render, including component templates.
                    $template = $this->renderingContext->getTemplateCompiler()->get($templateIdentifier);
                }
                Assert::assertSame($this->compiled, $template->isCompiled(), $originalTemplatePath ?? 'inline template');
                return $template;
            }
        });
        return $view;
    }
}
