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
            ['<my:testComponent title="TITLE" />', 'My test component TITLE'],
            ['<my:slotComponent>SLOT</my:slotComponent>', 'My test component SLOT'],
            ['<my:slotComponent><b>SLOT</b></my:slotComponent>', 'My test component <b>SLOT</b>'],
            ['<my:slotComponent />', 'My test component'],
            ['<my:nested.subComponent />', 'My sub component'],
            ['<my:recursive counter="5" />', '54321'],
            ['<my:additionalVariable />', "my additional value\nadditionalVariable"],
            ['<my:booleanArgument flag="1 == 1" />', 'true'],
            ['<my:booleanArgument flag="1 == 2" />', 'false'],
            ['<my:additionalArguments foo="bar" />', '{"foo":"bar","myAdditionalVariable":"my additional value","viewHelperName":"additionalArguments"}'],
        ];
    }

    #[Test]
    #[DataProvider('basicComponentCollectionDataProvider')]
    public function basicComponentCollection(string $source, string $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('my', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\BasicComponentCollection');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, trim($view->render()));

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('my', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\BasicComponentCollection');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, trim($view->render()));
    }

    public static function basicComponentCollectionValidatesArgumentsDataProvider(): iterable
    {
        return [
            'missing required argument' => ['<my:testComponent />', 1237823699],
            'additional argument not allowed' => ['<my:testComponent title="TITLE" foo="bar" />', 1748903732],
            'invalid type' => ['<my:testComponent title="TITLE" tags="test" />', 1746637333],
            'invalid component' => ['<my:nonexistentComponent />', 1407060572],
        ];
    }

    #[Test]
    #[DataProvider('basicComponentCollectionValidatesArgumentsDataProvider')]
    public function basicComponentCollectionValidatesArguments(string $source, int $expectedExceptionCode): void
    {
        self::expectExceptionCode($expectedExceptionCode);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('my', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\BasicComponentCollection');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->render();
    }

    #[Test]
    #[DataProvider('basicComponentCollectionValidatesArgumentsDataProvider')]
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
