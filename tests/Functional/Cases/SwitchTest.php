<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class SwitchTest extends AbstractFunctionalTestCase
{
    public static function ignoreTextAndWhitespacesDataProvider(): array
    {
        return [
            'Ignores whitespace inside parent switch outside case children' => [
                '<f:switch expression="1">   <f:case value="2">NO</f:case>   <f:case value="1">YES</f:case>   </f:switch>',
                '   ',
            ],
            'Ignores text inside parent switch outside case children' => [
                '<f:switch expression="1">TEXT<f:case value="2">NO</f:case><f:case value="1">YES</f:case></f:switch>',
                'TEXT',
            ],
            'Ignores text and whitespace inside parent switch outside case children 1' => [
                '<f:switch expression="1">   TEXT   <f:case value="2">NO</f:case><f:case value="1">YES</f:case></f:switch>',
                'TEXT',
            ],
            'Ignores text and whitespace inside parent switch outside case children 2' => [
                '<f:switch expression="1">   TEXT   <f:case value="2">NO</f:case><f:case value="1">YES</f:case></f:switch>',
                '   ',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider ignoreTextAndWhitespacesDataProvider
     */
    public function ignoreTextAndWhitespaces(string $source, string $notExpected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        self::assertStringNotContainsString($notExpected, $output);

        // Second run to test cached template parsing
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        self::assertStringNotContainsString($notExpected, $output);
    }
}
