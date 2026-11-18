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

final class TagBuilderChainingTest extends AbstractFunctionalTestCase
{
    public static function chainedTagBuilderCanBeMutatedDataProvider(): array
    {
        return [
            'tag syntax' => [
                '<test:tagMutation attributeValue="{second}"><test:tagBasedTest data="{first: \'one\'}">content</test:tagBasedTest></test:tagMutation>',
                ['second' => 'two'],
                '<div data-first="one" data-second="two">content</div>',
            ],
            'inline syntax' => [
                '{test:tagBasedTest(data: {first: \'one\'}) -> test:tagMutation(attributeValue: second)}',
                ['second' => 'two'],
                '<div data-first="one" data-second="two" />',
            ],
        ];
    }

    #[DataProvider('chainedTagBuilderCanBeMutatedDataProvider')]
    #[Test]
    public function chainedTagBuilderCanBeMutated(string $source, array $variables, string $expected): void
    {
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        self::assertSame($expected, $view->render(), 'uncached');

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        self::assertSame($expected, $view->render(), 'cached');
    }
}
