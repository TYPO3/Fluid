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
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\BackedEnumExample;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\ClassConstantsExample;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\EnumExample;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ConstantViewHelperTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function renderThrowsExceptionOnNonStringValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $name = new \stdClass();
        $view = new TemplateView();
        $view->assignMultiple(['name' => $name]);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:constant name="{name}" />');
        $view->render();
    }

    #[Test]
    public function renderThrowsErrorOnUndefinedConstant(): void
    {
        $this->expectException(\Error::class);
        $name = 'FOO';
        $view = new TemplateView();
        $view->assignMultiple(['name' => $name]);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:constant name="{name}" />');
        $view->render();
    }

    public static function renderDataProvider(): \Generator
    {
        yield 'Name is built-in PHP constant' => [
            'PHP_INT_MAX',
            PHP_INT_MAX,
        ];

        yield 'Name is class constant w/out leading slash' => [
            'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\ClassConstantsExample::FOO',
            ClassConstantsExample::FOO,
        ];

        yield 'Name is class constant with leading slash' => [
            '\TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\ClassConstantsExample::FOO',
            ClassConstantsExample::FOO,
        ];

        yield 'Name is backed enum case w/out leading slash' => [
            'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\BackedEnumExample::BAR',
            BackedEnumExample::BAR,
        ];

        yield 'Name is backed enum case with leading slash' => [
            '\TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\BackedEnumExample::BAR',
            BackedEnumExample::BAR,
        ];

        yield 'Name is enum case w/out leading slash' => [
            'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\EnumExample::FOO',
            EnumExample::FOO,
        ];

        yield 'Name is enum case with leading slash' => [
            '\TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\EnumExample::FOO',
            EnumExample::FOO,
        ];
    }

    #[DataProvider('renderDataProvider')]
    #[Test]
    public function render(mixed $name, mixed $expected): void
    {
        $templateSources = [
            '<f:constant name="{name}" />',
            '<f:constant>{name}</f:constant>',
            '{f:constant(name: \'{name}\')}',
        ];

        foreach ($templateSources as $templateSource) {
            $view = new TemplateView();
            $view->assignMultiple(['name' => $name]);
            $view->getRenderingContext()->setCache(self::$cache);
            $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
            self::assertSame($expected, $view->render());

            $view = new TemplateView();
            $view->assignMultiple(['name' => $name]);
            $view->getRenderingContext()->setCache(self::$cache);
            $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($templateSource);
            self::assertSame($expected, $view->render());
        }
    }
}
