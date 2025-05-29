<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ArgumentViewHelperTest extends AbstractFunctionalTestCase
{
    public static function templateWithArgumentDefinitionsDataProvider(): iterable
    {
        return [
            'all parameters provided with correct types' => [
                ['title' => 'My title', 'tags' => ['tag1', 'tag2'], 'user' => 'me'],
                ['title' => 'My title', 'tags' => ['tag1', 'tag2'], 'user' => 'me', 'flag' => null],
            ],
            'all parameters provided with type conversion' => [
                ['title' => 123, 'tags' => ['tag1', 'tag2'], 'user' => 1.23],
                ['title' => '123', 'tags' => ['tag1', 'tag2'], 'user' => '1.23', 'flag' => null],
            ],
            'fallback to default value' => [
                ['title' => 'My title', 'tags' => ['tag1', 'tag2']],
                ['title' => 'My title', 'tags' => ['tag1', 'tag2'], 'user' => 'admin', 'flag' => null],
            ],
            'optional parameter not provided' => [
                ['title' => 'My title'],
                ['title' => 'My title', 'tags' => null, 'user' => 'admin', 'flag' => null],
            ],
            'additional parameter provided' => [
                ['title' => 'My title', 'additional' => 'foo'],
                ['title' => 'My title', 'additional' => 'foo', 'tags' => null, 'user' => 'admin', 'flag' => null],
            ],
            'boolean true' => [
                ['title' => 'My title', 'flag' => true],
                ['title' => 'My title', 'flag' => true, 'tags' => null, 'user' => 'admin'],
            ],
            'boolean false' => [
                ['title' => 'My title', 'flag' => false],
                ['title' => 'My title', 'flag' => false, 'tags' => null, 'user' => 'admin'],
            ],
            'boolean as integer true' => [
                ['title' => 'My title', 'flag' => 1],
                ['title' => 'My title', 'flag' => true, 'tags' => null, 'user' => 'admin'],
            ],
            'boolean as integer false' => [
                ['title' => 'My title', 'flag' => 0],
                ['title' => 'My title', 'flag' => false, 'tags' => null, 'user' => 'admin'],
            ],
            'boolean as expression true' => [
                ['title' => 'My title', 'flag' => '1 == 1'],
                ['title' => 'My title', 'flag' => true, 'tags' => null, 'user' => 'admin'],
            ],
            'boolean as expression false' => [
                ['title' => 'My title', 'flag' => '1 == 0'],
                ['title' => 'My title', 'flag' => false, 'tags' => null, 'user' => 'admin'],
            ],
            'boolean as expression with variable true' => [
                ['title' => 'My title', 'flag' => '{title} === \'My title\''],
                ['title' => 'My title', 'flag' => true, 'tags' => null, 'user' => 'admin'],
            ],
            'boolean as expression with variable false' => [
                ['title' => 'My title', 'flag' => '{title} === \'Something else\''],
                ['title' => 'My title', 'flag' => false, 'tags' => null, 'user' => 'admin'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('templateWithArgumentDefinitionsDataProvider')]
    public function templateWithArgumentDefinitions(array $variables, array $expected): void
    {
        $templatePath = __DIR__ . '/../Fixtures/Templates/TemplateWithArgumentDefinitions.html';
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename($templatePath);
        self::assertSame($expected, json_decode(trim($view->render()), true));

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename($templatePath);
        self::assertSame($expected, json_decode(trim($view->render()), true));
    }

    #[Test]
    #[DataProvider('templateWithArgumentDefinitionsDataProvider')]
    public function partialWithArgumentDefinitions(array $variables, array $expected): void
    {
        $templateSource = '<f:render partial="PartialWithArgumentDefinitions" arguments="{_all}" />';
        $partialRootPath = __DIR__ . '/../Fixtures/Partials/';
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([$partialRootPath]);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
        self::assertSame($expected, json_decode(trim($view->render()), true));

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([$partialRootPath]);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
        self::assertSame($expected, json_decode(trim($view->render()), true));
    }

    #[Test]
    #[DataProvider('templateWithArgumentDefinitionsDataProvider')]
    public function layoutWithArgumentDefinitions(array $variables, array $expected): void
    {
        $templateSource = '<f:layout name="LayoutWithArgumentDefinitions" />';
        $layoutRootPath = __DIR__ . '/../Fixtures/Layouts/';
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([$layoutRootPath]);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
        self::assertSame($expected, json_decode(trim($view->render()), true));

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([$layoutRootPath]);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
        self::assertSame($expected, json_decode(trim($view->render()), true));
    }

    public static function templateWithInvalidArgumentsDataProvider(): iterable
    {
        return [
            'required argument not provided' => [
                [],
                1746637334,
            ],
            'invalid type provided' => [
                ['title' => 'My title', 'user' => ['firstName' => 'Jane', 'lastName' => 'Doe']],
                1746637333,
            ],
        ];
    }

    #[Test]
    #[DataProvider('templateWithInvalidArgumentsDataProvider')]
    public function templateWithInvalidArguments(array $variables, int $expectedExceptionCode): void
    {
        self::expectException(\TYPO3Fluid\Fluid\View\Exception::class);
        self::expectExceptionCode($expectedExceptionCode);
        $templatePath = __DIR__ . '/../Fixtures/Templates/TemplateWithArgumentDefinitions.html';
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename($templatePath);
        $view->render();
    }

    #[Test]
    #[DataProvider('templateWithInvalidArgumentsDataProvider')]
    public function partialWithInvalidArguments(array $variables, int $expectedExceptionCode): void
    {
        self::expectException(\TYPO3Fluid\Fluid\View\Exception::class);
        self::expectExceptionCode($expectedExceptionCode);
        $templateSource = '<f:render partial="PartialWithArgumentDefinitions" arguments="{_all}" />';
        $partialRootPath = __DIR__ . '/../Fixtures/Partials/';
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([$partialRootPath]);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
        $view->render();
    }

    #[Test]
    public function partialArgumentsAreIgnoredWithSection(): void
    {
        $templateSource = '<f:render partial="PartialWithArgumentDefinitionsAndSection" section="test" />';
        $partialRootPath = __DIR__ . '/../Fixtures/Partials/';
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([$partialRootPath]);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
        self::assertSame('section rendered', $view->render());

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([$partialRootPath]);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
        self::assertSame('section rendered', $view->render());
    }

    #[Test]
    #[DataProvider('templateWithInvalidArgumentsDataProvider')]
    public function layoutWithInvalidArguments(array $variables, int $expectedExceptionCode): void
    {
        self::expectException(\TYPO3Fluid\Fluid\View\Exception::class);
        self::expectExceptionCode($expectedExceptionCode);
        $templateSource = '<f:layout name="LayoutWithArgumentDefinitions" />';
        $layoutRootPath = __DIR__ . '/../Fixtures/Layouts/';
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([$layoutRootPath]);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
        $view->render();
    }

    public static function templateArgumentsAreIgnoredWithLayoutDataProvider(): iterable
    {
        return [
            'layout without section' => [
                '<f:layout name="LayoutWithArgumentDefinitions" /><f:argument name="requiredTemplateArgument" type="string" />',
            ],
            'layout with section' => [
                '<f:layout name="LayoutWithArgumentDefinitionsCallingSection" /><f:argument name="requiredTemplateArgument" type="string" /><f:section name="test"></f:section>',
            ],
        ];
    }

    #[Test]
    #[DataProvider('templateArgumentsAreIgnoredWithLayoutDataProvider')]
    public function templateArgumentsAreIgnoredWithLayout(string $templateSource): void
    {
        $layoutRootPath = __DIR__ . '/../Fixtures/Layouts/';
        $variables = ['title' => 'My title', 'tags' => ['tag1', 'tag2'], 'user' => 'me', 'flag' => null];
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([$layoutRootPath]);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
        self::assertSame($variables, json_decode(trim($view->render()), true));

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setLayoutRootPaths([$layoutRootPath]);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
        self::assertSame($variables, json_decode(trim($view->render()), true));
    }

    public static function booleanParserHasContextDataProvider(): iterable
    {
        return [
            [
                '<f:render partial="PartialWithArgumentDefinitions" arguments="{title: \'\', flag: \'{myVariable} == \\\'foo\\\'\'}" />',
                ['myVariable' => 'foo'],
                ['title' => '', 'flag' => true, 'tags' => null, 'user' => 'admin'],
            ],
            [
                '<f:render partial="PartialWithArgumentDefinitions" arguments="{title: \'\', flag: \'{myVariable} == \\\'foo\\\'\'}" />',
                ['myVariable' => 'bar'],
                ['title' => '', 'flag' => false, 'tags' => null, 'user' => 'admin'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('booleanParserHasContextDataProvider')]
    public function booleanParserHasContext(string $templateSource, array $variables, array $expected): void
    {
        $partialRootPath = __DIR__ . '/../Fixtures/Partials/';
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([$partialRootPath]);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
        self::assertSame($expected, json_decode(trim($view->render()), true), 'uncached');

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([$partialRootPath]);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
        self::assertSame($expected, json_decode(trim($view->render()), true), 'cached');
    }
}
