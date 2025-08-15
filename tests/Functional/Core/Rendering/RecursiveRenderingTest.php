<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Rendering;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class RecursiveRenderingTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function recursiveSectionRenderingClonesVariableStorageAndRestoresAfterLoop(): void
    {
        $source = file_get_contents(__DIR__ . '/../../Fixtures/Templates/RecursiveSectionRendering.html');
        $variables = [
            'settings' => [
                'test' => '<strong>Bla</strong>',
            ],
            'items' => [
                [
                    'id' => 1,
                    'items' => [
                        [
                            'id' => 2,
                            'items' => [
                                [
                                    'id' => 3,
                                    'items' => [],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 4,
                ],
            ],
        ];
        $expectations = [
            'Item: 1.',
            'Item: 2.',
            'Item: 3.',
            'Item: 4.',
        ];

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->assignMultiple($variables);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        foreach ($expectations as $expectedValue) {
            self::assertStringContainsString($expectedValue, $output);
        }

        // A second run to verify cached template parsing
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->assignMultiple($variables);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        foreach ($expectations as $expectedValue) {
            self::assertStringContainsString($expectedValue, $output);
        }
    }

    #[Test]
    public function recursivePartialRenderingClonesVariableStorageAndRestoresAfterLoop(): void
    {
        $partialPath = __DIR__ . '/../../Fixtures/Partials/';
        $source = file_get_contents(__DIR__ . '/../../Fixtures/Templates/RecursivePartialRendering.html');
        $variables = [
            'settings' => [
                'test' => '<strong>Bla</strong>',
            ],
            'items' => [
                [
                    'id' => 1,
                    'items' => [
                        [
                            'id' => 2,
                            'items' => [
                                [
                                    'id' => 3,
                                    'items' => [],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 4,
                ],
            ],
        ];
        $expectations = [
            'Item: 1.',
            'Item: 2.',
            'Item: 3.',
            'Item: 4.',
        ];

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->assignMultiple($variables);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([$partialPath]);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        foreach ($expectations as $expectedValue) {
            self::assertStringContainsString($expectedValue, $output);
        }

        // A second run to verify cached template parsing
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->assignMultiple($variables);
        $view->getRenderingContext()->getTemplatePaths()->setPartialRootPaths([$partialPath]);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        foreach ($expectations as $expectedValue) {
            self::assertStringContainsString($expectedValue, $output);
        }
    }

    #[Test]
    public function recursiveSectionsProvideCorrectViewHelperArguments(): void
    {
        $source
            = '<f:section name="Test">'
                . '<test:tagBasedTest data-context="{testVar}">'
                    . '{testVar}'
                    . '<f:if condition="{testVar} == \'outer\'">'
                        . '<f:render section="Test" arguments="{testVar: \'inner\'}" />'
                    . '</f:if>'
                . '</test:tagBasedTest>'
            . '</f:section>'
            . '<f:render section="Test" arguments="{testVar: \'outer\'}" />';
        // @todo this should be the correct output for uncached as well, currently arguments are overwritten by the
        //       inner ViewHelper call
        // $expected = '<div data-context="outer">outer<div data-context="inner">inner</div></div>';
        $expected = '<div data-context="inner">outer<div data-context="inner">inner</div></div>';

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render(), 'uncached');

        // @todo remove this once uncached behaves consistently
        $expected = '<div data-context="outer">outer<div data-context="inner">inner</div></div>';
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render(), 'cached');
    }
}
