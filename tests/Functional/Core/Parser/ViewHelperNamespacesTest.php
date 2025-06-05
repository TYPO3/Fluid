<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ViewHelperNamespacesTest extends AbstractFunctionalTestCase
{
    public static function getTemplateCodeFixturesAndExpectations(): array
    {
        return [
            'Ignoring namespaces without conflict with registered namespace' => [
                '{namespace z*}{namespace bar}<zoo:bar /><bar:foo></bar:foo><zoo.bar:baz />{zoo:bar()}{bar:foo()}{zoo.bar:baz()}<f:format.raw>foobar</f:format.raw>',
                '<zoo:bar /><bar:foo></bar:foo><zoo.bar:baz />{zoo:bar()}{bar:foo()}{zoo.bar:baz()}foobar',
            ],
            'Ignoring namespaces with conflict with registered namespace gives registered namespace priority' => [
                '{namespace f*}{namespace bar}<foo:bar /><bar:foo></bar:foo><foo.bar:baz />{bar:foo()}{foo.bar:baz()}<f:format.raw>foobar</f:format.raw>',
                '<foo:bar /><bar:foo></bar:foo><foo.bar:baz />{bar:foo()}{foo.bar:baz()}foobar',
            ],
        ];
    }

    #[DataProvider('getTemplateCodeFixturesAndExpectations')]
    #[Test]
    public function testTemplateCodeFixture(string $source, string $expectedInOutput): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        self::assertSame($expectedInOutput, $output);

        // Second run to verify cached behavior.
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        self::assertSame($expectedInOutput, $output);
    }

    public static function inlineViewHelperSyntaxDataProvider(): array
    {
        return [
            'Single inline ViewHelper' => [
                '{test:contentArgumentName(value: input)}',
                'input',
            ],
            'Single inline ViewHelper, content argument' => [
                '{input -> test:contentArgumentName()}',
                'input',
            ],
            'Two inline ViewHelpers' => [
                '{test:contentArgumentName(value: input) -> f:format.json()}',
                '&quot;input&quot;',
            ],
            'Two inline ViewHelpers, content argument' => [
                '{input -> test:contentArgumentName() -> f:format.json()}',
                '&quot;input&quot;',
            ],
            'Multiple inline ViewHelpers' => [
                '{test:contentArgumentName(value: input) -> f:format.json() -> f:format.raw()}',
                '"input"',
            ],
            'Multiple inline ViewHelpers, content argument' => [
                '{input -> test:contentArgumentName() -> f:format.json() -> f:format.raw()}',
                '"input"',
            ],

            'Single inline ViewHelper, nested input variable' => [
                '{test:contentArgumentName(value: nestedInput.0)}',
                'input',
            ],
            'Single inline ViewHelper, content argument, nested input variable' => [
                '{nestedInput.0 -> test:contentArgumentName()}',
                'input',
            ],
            'Two inline ViewHelpers, nested input variable' => [
                '{test:contentArgumentName(value: nestedInput.0) -> f:format.json()}',
                '&quot;input&quot;',
            ],
            'Two inline ViewHelpers, content argument, nested input variable' => [
                '{nestedInput.0 -> test:contentArgumentName() -> f:format.json()}',
                '&quot;input&quot;',
            ],
            'Multiple inline ViewHelpers, nested input variable' => [
                '{test:contentArgumentName(value: nestedInput.0) -> f:format.json() -> f:format.raw()}',
                '"input"',
            ],
            'Multiple inline ViewHelpers, content argument, nested input variable' => [
                '{nestedInput.0 -> test:contentArgumentName() -> f:format.json() -> f:format.raw()}',
                '"input"',
            ],
        ];
    }

    #[Test]
    #[DataProvider('inlineViewHelperSyntaxDataProvider')]
    public function inlineViewHelperSyntax(string $source, string $expected): void
    {
        $view = new TemplateView();
        $view->assign('input', 'input');
        $view->assign('nestedInput', ['input']);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        self::assertSame($expected, $output);

        // Second run to verify cached behavior.
        $view = new TemplateView();
        $view->assign('input', 'input');
        $view->assign('nestedInput', ['input']);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        self::assertSame($expected, $output);
    }

    #[Test]
    #[DataProvider('inlineViewHelperSyntaxDataProvider')]
    public function inlineViewHelperSyntaxIgnoredNamespace(string $source, string $expected): void
    {
        // Ugly compiler detail: Because the templates would be exactly the same, the both
        // test methods would share the compiler's runtime cache, which is not what we want.
        $cacheBustingPrefix = 'ignored';

        $view = new TemplateView();
        $view->assign('input', 'input');
        $view->assign('nestedInput', ['input']);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', null);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($cacheBustingPrefix . $source);
        $output = $view->render();
        self::assertSame($cacheBustingPrefix . $source, $output);

        // Second run to verify cached behavior.
        $view = new TemplateView();
        $view->assign('input', 'input');
        $view->assign('nestedInput', ['input']);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', null);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($cacheBustingPrefix . $source);
        $output = $view->render();
        self::assertSame($cacheBustingPrefix . $source, $output);
    }
}
