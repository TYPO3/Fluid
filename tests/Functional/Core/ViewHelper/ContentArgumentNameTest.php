<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\ViewHelper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ContentArgumentNameTest extends AbstractFunctionalTestCase
{
    public static function renderDataProvider(): array
    {
        return [
            'self-closing tag without argument value' => ['<test:contentArgumentName />', null],
            'only children' => ['<test:contentArgumentName>child value</test:contentArgumentName>', 'child value'],
            'children and empty argument value' => ['<test:contentArgumentName value="">child value</test:contentArgumentName>', ''],
            'children and argument value' => ['<test:contentArgumentName value="argument value">child value</test:contentArgumentName>', 'argument value'],
            'self-closing tag with argument value' => ['<test:contentArgumentName value="argument value" />', 'argument value'],
            'empty children and argument value' => ['<test:contentArgumentName value="argument value"></test:contentArgumentName>', 'argument value'],
        ];
    }

    #[DataProvider('renderDataProvider')]
    #[Test]
    public function render(string $source, ?string $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render());
    }
}