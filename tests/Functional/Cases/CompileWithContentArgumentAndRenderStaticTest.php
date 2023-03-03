<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class CompileWithContentArgumentAndRenderStaticTest extends AbstractFunctionalTestCase
{
    public static function compileWithContentArgumentAndRenderStaticDataProvider(): array
    {
        return [
            // with trait but without contentArgumentProperty set in viewhelper and having optional argument
            'children content but no argument value' => [
                '<test:compileWithContentArgumentAndRenderStaticUseFirstRegisteredOptionalArgumentAsRenderChildren>mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticUseFirstRegisteredOptionalArgumentAsRenderChildren>',
                [
                    '"arguments[firstOptionalArgument]": null',
                    '"renderChildrenClosure": "mustBeRenderingChildrenClosure"',
                ],
            ],
            'children content and argument value' => [
                '<test:compileWithContentArgumentAndRenderStaticUseFirstRegisteredOptionalArgumentAsRenderChildren firstOptionalArgument="firstOptionalArgument">mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticUseFirstRegisteredOptionalArgumentAsRenderChildren>',
                [
                    '"arguments[firstOptionalArgument]": "firstOptionalArgument"',
                    '"renderChildrenClosure": "firstOptionalArgument"',
                ],
            ],
            // with trait but without contentArgumentProperty set in viewhelper and having first optional argument as second argument
            'children content but no argument value [optional is second argument]' => [
                '<test:compileWithContentArgumentAndRenderStaticFirstRegisteredOptionalArgumentAfterRequiredArgumentAsRenderChildren requiredArgument="dummy">mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticFirstRegisteredOptionalArgumentAfterRequiredArgumentAsRenderChildren>',
                [
                    '"arguments[firstOptionalArgument]": null',
                    '"renderChildrenClosure": "mustBeRenderingChildrenClosure"',
                ],
            ],
            'children content and argument value [optional is second argument]' => [
                '<test:compileWithContentArgumentAndRenderStaticFirstRegisteredOptionalArgumentAfterRequiredArgumentAsRenderChildren requiredArgument="dummy" firstOptionalArgument="firstOptionalArgument">mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticFirstRegisteredOptionalArgumentAfterRequiredArgumentAsRenderChildren>',
                [
                    '"arguments[firstOptionalArgument]": "firstOptionalArgument"',
                    '"renderChildrenClosure": "firstOptionalArgument"',
                ],
            ],
            // now the hard cases - setting the contentArgumentName property through the constructor
            'children content but no argument value [use second optional argument][explicit set in __construct]' => [
                '<test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentInConstructor>mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentInConstructor>',
                [
                    '"arguments[firstOptionalArgument]": null',
                    '"arguments[secondOptionalArgument]": null',
                    '"renderChildrenClosure": "mustBeRenderingChildrenClosure"',
                ],
            ],
            'children content and argument value [use second optional argument][explicit set in __construct]' => [
                '<test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentInConstructor firstOptionalArgument="firstOptionalArgument" secondOptionalArgument="secondOptionalArgument">mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentInConstructor>',
                [
                    '"arguments[firstOptionalArgument]": "firstOptionalArgument"',
                    '"arguments[secondOptionalArgument]": "secondOptionalArgument"',
                    '"renderChildrenClosure": "secondOptionalArgument"',
                ],
            ],
            // now the hard cases - setting the contentArgumentName property through overriding resolveContentArgumentName
            'children content but no argument value [use second optional argument][explicit set in overriden resolveContentArgumentName]' => [
                '<test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentOverriddenResolveContentArgumentNameMethod>mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentOverriddenResolveContentArgumentNameMethod>',
                [
                    '"arguments[firstOptionalArgument]": null',
                    '"arguments[secondOptionalArgument]": null',
                    '"renderChildrenClosure": "mustBeRenderingChildrenClosure"',
                ],
            ],
            'children content and argument value [use second optional argument][explicit set in overriden resolveContentArgumentName]' => [
                '<test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentOverriddenResolveContentArgumentNameMethod firstOptionalArgument="firstOptionalArgument" secondOptionalArgument="secondOptionalArgument">mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentOverriddenResolveContentArgumentNameMethod>',
                [
                    '"arguments[firstOptionalArgument]": "firstOptionalArgument"',
                    '"arguments[secondOptionalArgument]": "secondOptionalArgument"',
                    '"renderChildrenClosure": "secondOptionalArgument"',
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider compileWithContentArgumentAndRenderStaticDataProvider
     */
    public function compileWithContentArgumentAndRenderStatic(string $source, array $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $output = $view->render();
        foreach ($expected as $expectedValue) {
            self::assertStringContainsString($expectedValue, $output);
        }

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $output = $view->render();
        foreach ($expected as $expectedValue) {
            self::assertStringContainsString($expectedValue, $output);
        }
    }
}
