<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Component;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ComponentRenderingTest extends AbstractFunctionalTestCase
{
    public static function basicComponentCollectionDataProvider(): iterable
    {
        return [
            // parameters and escaping
            'defined argument' => ['<my:testComponent title="TITLE" />', "\n\n\nMy test component TITLE\n"],
            'defined argument with inline HTML escaped correctly' => ['<my:testComponent title="<b>TITLE</b>" />', "\n\n\nMy test component &lt;b&gt;TITLE&lt;/b&gt;\n"],
            'defined argument with predefined HTML variable escaped correctly' => ['<my:testComponent title="{unsafeInput}" />', "\n\n\nMy test component &lt;script&gt;alert(&#039;This JavaScript should not be executed by the browser&#039;)&lt;/script&gt;\n"],
            'defined argument with HTML variable escaped correctly' => ['<f:variable name="myHtml"><b>TITLE</b></f:variable><my:testComponent title="{myHtml}" />', "\n\n\nMy test component &lt;b&gt;TITLE&lt;/b&gt;\n"],
            'defined argument with raw HTML variable escaped correctly' => ['<f:variable name="myHtml"><b>TITLE</b></f:variable><my:testComponent title="{myHtml -> f:format.raw()}" />', "\n\n\nMy test component &lt;b&gt;TITLE&lt;/b&gt;\n"],
            'undefined argument with inline HTML escaped correctly' => ['<my:additionalArguments foo="<b>BAR</b>" />', "&lt;b&gt;BAR&lt;/b&gt;\n"],
            'undefined argument with predefined HTML variable escaped correctly' => ['<my:additionalArguments foo="{unsafeInput}" />', "&lt;script&gt;alert(&#039;This JavaScript should not be executed by the browser&#039;)&lt;/script&gt;\n"],
            'undefined argument with HTML variable escaped correctly' => ['<f:variable name="myHtml"><b>BAR</b></f:variable><my:additionalArguments foo="{myHtml}" />', "&lt;b&gt;BAR&lt;/b&gt;\n"],
            'undefined argument with raw HTML variable escaped correctly' => ['<f:variable name="myHtml"><b>BAR</b></f:variable><my:additionalArguments foo="{myHtml -> f:format.raw()}" />', "&lt;b&gt;BAR&lt;/b&gt;\n"],
            'truthy boolean argument processed by BooleanParser' => ['<my:booleanArgument flag="1 == 1" />', "\n\ntrue\n\n"],
            'falsy boolean argument processed by BooleanParser' => ['<my:booleanArgument flag="1 == 2" />', "\n\n\nfalse\n"],
            'format.raw inside component avoids argument escaping' => ['<my:rawVariable raw="{unsafeInput}" />', "\n\n<script>alert('This JavaScript should not be executed by the browser')</script>\n"],

            // slots and escaping
            'self-closing component with slot' => ['<my:slotComponent />', "My test component |slot is not defined|\n"],
            // @todo This is an unfortunate shortcoming of the Fluid parser. Ideally, slot should be defined, but with empty string
            'empty component with slot' => ['<my:slotComponent></my:slotComponent>', "My test component |slot is not defined|\n"],
            'only whitespace provided as slot content' => ['<my:slotComponent> </my:slotComponent>', "My test component | |\n"],
            'string as slot content' => ['<my:slotComponent>SLOT</my:slotComponent>', "My test component |SLOT|\n"],
            'inline HTML as slot content' => ['<my:slotComponent><b>SLOT</b></my:slotComponent>', "My test component |<b>SLOT</b>|\n"],
            'HTML variable as slot escaped correctly' => ['<f:variable name="myHtml"><b>SLOT</b></f:variable><my:slotComponent>{myHtml}</my:slotComponent>', "My test component |&lt;b&gt;SLOT&lt;/b&gt;|\n"],
            'predefined HTML variable as slot escaped correctly' => ['<my:slotComponent>{unsafeInput}</my:slotComponent>', "My test component |&lt;script&gt;alert(&#039;This JavaScript should not be executed by the browser&#039;)&lt;/script&gt;|\n"],
            'raw HTML variable as slot not escaped' => ['<f:variable name="myHtml"><b>SLOT</b></f:variable><my:slotComponent>{myHtml -> f:format.raw()}</my:slotComponent>', "My test component |<b>SLOT</b>|\n"],
            'nested components' => ['<my:slotComponent><my:nested.subComponent /></my:slotComponent>', "My test component |\n\nMy <b>sub</b> component\n|\n"],
            'named slots' => ['<my:namedSlots><f:fragment name="test1">content1</f:fragment><f:fragment name="test2">content2</f:fragment><f:fragment>defaultContent</f:fragment></my:namedSlots>', "|content1|content2|defaultContent|\n"],
            'undefined named slot' => ['<my:namedSlots><f:fragment name="test1"><b>content1</b></f:fragment></my:namedSlots>', "|<b>content1</b>|||\n"],
            'multiple slots, only default given' => ['<my:namedSlots><b>defaultContent</b></my:namedSlots>', "|||<b>defaultContent</b>|\n"],
            'multiple slots, only default given as fragment' => ['<my:namedSlots><f:fragment><b>defaultContent</b></f:fragment></my:namedSlots>', "|||<b>defaultContent</b>|\n"],
            'HTML variable in named slot escaped correctly' => ['<f:variable name="myHtml"><b>SLOT</b></f:variable><my:namedSlots><f:fragment name="test1">{myHtml}</f:fragment></my:namedSlots>', "|&lt;b&gt;SLOT&lt;/b&gt;|||\n"],
            'predefined HTML variable in named slot escaped correctly' => ['<my:namedSlots><f:fragment name="test1">{unsafeInput}</f:fragment></my:namedSlots>', "|&lt;script&gt;alert(&#039;This JavaScript should not be executed by the browser&#039;)&lt;/script&gt;|||\n"],
            'raw HTML variable in named slot not escaped' => ['<f:variable name="myHtml"><b>SLOT</b></f:variable><my:namedSlots><f:fragment name="test1">{myHtml -> f:format.raw()}</f:fragment></my:namedSlots>', "|<b>SLOT</b>|||\n"],
            'nested components in named slots' => ['<my:namedSlots><f:fragment name="test1"><my:nested.subComponent /></f:fragment><f:fragment><my:nested.subComponent /></f:fragment></my:namedSlots>', "|\n\nMy <b>sub</b> component\n||\n\nMy <b>sub</b> component\n|\n"],
            'undefined named slot gets ignored' => ['<my:namedSlots><f:fragment name="foo">test</f:fragment></my:namedSlots>', "||||\n"],
            'additional content gets ignored if fragment is used' => ['<my:namedSlots><f:fragment name="test1">content1</f:fragment>bar</my:namedSlots>', "|content1|||\n"],

            // other cases
            'component in subfolder' => ['<my:nested.subComponent />', "\n\nMy <b>sub</b> component\n"],
            'recursive call of one component' => ['<f:format.trim><my:recursive counter="5" /></f:format.trim>', '54321'],
            'access to variables provided by delegate' => ['<my:additionalVariable />', "my additional value\nadditionalVariable\n"],
            'additional arguments can be provided if delegate allows' => ['<my:additionalArgumentsJson foo="bar" />', '{"foo":"bar","myAdditionalVariable":"my additional value","viewHelperName":"additionalArgumentsJson"}' . "\n"],
        ];
    }

    #[Test]
    #[DataProvider('basicComponentCollectionDataProvider')]
    public function basicComponentCollection(string $source, string $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('my', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\BasicComponentCollection');
        $view->getRenderingContext()->getVariableProvider()->add('unsafeInput', "<script>alert('This JavaScript should not be executed by the browser')</script>");
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render(), 'uncached');

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('my', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\BasicComponentCollection');
        $view->getRenderingContext()->getVariableProvider()->add('unsafeInput', "<script>alert('This JavaScript should not be executed by the browser')</script>");
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render(), 'cached');
    }

    public static function basicComponentCollectionValidatesArgumentsCachedDataProvider(): iterable
    {
        return [
            'missing required argument' => ['<my:testComponent />', 1237823699],
            'additional argument not allowed' => ['<my:testComponent title="TITLE" foo="bar" />', 1748903732],
            'invalid type' => ['<my:testComponent title="TITLE" tags="test" />', 1746637333], // different exception code between uncached and cached
            'invalid component' => ['<my:nonexistentComponent />', 1407060572],
            'fragments nested in other viewhelpers' => ['<my:namedSlots><f:if condition="1 == 0"><f:fragment>foo</f:fragment></f:if></my:namedSlots>', 1750865702],
        ];
    }

    #[Test]
    #[DataProvider('basicComponentCollectionValidatesArgumentsUncachedDataProvider')]
    public function basicComponentCollectionValidatesArgumentsUncached(string $source, int $expectedExceptionCode): void
    {
        self::expectExceptionCode($expectedExceptionCode);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('my', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\BasicComponentCollection');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->render();
    }

    public static function basicComponentCollectionValidatesArgumentsUncachedDataProvider(): iterable
    {
        return [
            'missing required argument' => ['<my:testComponent />', 1237823699],
            'additional argument not allowed' => ['<my:testComponent title="TITLE" foo="bar" />', 1748903732],
            'invalid type' => ['<my:testComponent title="TITLE" tags="test" />', 1256475113], // different exception code between uncached and cached
            'invalid component' => ['<my:nonexistentComponent />', 1407060572],
            'fragments nested in other viewhelpers' => ['<my:namedSlots><f:if condition="1 == 0"><f:fragment>foo</f:fragment></f:if></my:namedSlots>', 1750865702],
            // Some detail validations can only be performed for uncached templates because
            // the required information is no longer reproducible from the cache
            'duplicate fragment' => ['<my:namedSlots><f:fragment name="test1">foo</f:fragment><f:fragment name="test1">bar</f:fragment></my:namedSlots>', 1750865701],
            'duplicate default fragment' => ['<my:namedSlots><f:fragment>foo</f:fragment><f:fragment>bar</f:fragment></my:namedSlots>', 1750865701],
        ];
    }

    #[Test]
    #[DataProvider('basicComponentCollectionValidatesArgumentsCachedDataProvider')]
    public function basicComponentCollectionValidatesArgumentsCached(string $source, int $expectedExceptionCode): void
    {
        self::expectExceptionCode($expectedExceptionCode);

        try {
            $view = new TemplateView();
            $view->getRenderingContext()->setCache(self::$cache);
            $view->getRenderingContext()->getViewHelperResolver()->addNamespace('my', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\BasicComponentCollection');
            $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
            $view->render();
        } catch (\Exception) {
        }

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('my', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\BasicComponentCollection');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->render();
    }

    public static function jsonComponentCollectionDataProvider(): iterable
    {
        return [
            ['<json:testComponent title="TITLE" />', '{"component":"testComponent","arguments":{"title":"TITLE"}}'],
            ['<json:sub.component />', '{"component":"sub.component","arguments":[]}'],
        ];
    }

    #[Test]
    #[DataProvider('jsonComponentCollectionDataProvider')]

    public function jsonComponentCollection(string $source, string $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('json', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\JsonComponentCollection');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, trim($view->render()));

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('json', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\JsonComponentCollection');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, trim($view->render()));
    }
}
