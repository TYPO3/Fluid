<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class RecursiveSectionRenderingTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function recursiveSectionRenderingClonesVariableStorageAndRestoresAfterLoop(): void
    {
        $source = file_get_contents(__DIR__ . '/../../Fixtures/Templates/RecursiveSectionRendering.html');
        $variables = [
            'settings' => [
                'test' => '<strong>Bla</strong>'
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
                                    'items' => []
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 4
                ]
            ]
        ];
        $expectations = [
            'Item: 1.',
            'Item: 2.',
            'Item: 3.',
            'Item: 4.'
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
}
