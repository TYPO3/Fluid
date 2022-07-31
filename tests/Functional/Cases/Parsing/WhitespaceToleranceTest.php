<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Parsing;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class WhitespaceToleranceTest extends AbstractFunctionalTestCase
{
    public function whitespaceToleranceDataProvider(): array
    {
        return [
            'Normal expected whitespace tolerance' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="works" />',
            ],
            'No whitespace before self-close of tag' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="works"/>',
            ],
            'Extra whitespace before self-close of tag' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="works"      />',
            ],
            'Extra whitespace before argument name' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="works" />',
            ],
            'Extra whitespace after argument name' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content    ="works" />',
            ],
            'Extra whitespace before argument value' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content= "works" />',
            ],
            'Extra whitespace after argument name and before argument value' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content = "works" />',
            ],
            'Extra whitespace before and after argument name' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled  content ="works" />',
            ],
            'Extra whitespace before argument name and after argument name and before argument value' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled  content = "works" />',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider whitespaceToleranceDataProvider
     */
    public function whitespaceTolerance(string $source): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $output = $view->render();
        self::assertStringContainsString('works', $output);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $output = $view->render();
        self::assertStringContainsString('works', $output);
    }
}
