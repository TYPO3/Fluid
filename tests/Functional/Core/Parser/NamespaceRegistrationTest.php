<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class NamespaceRegistrationTest extends AbstractFunctionalTestCase
{
    public static function getTemplateCodeFixturesAndExpectations(): array
    {
        return [
            'Ignoring namespaces without conflict with registered namespace' => [
                '{namespace z*}{namespace bar}<zoo:bar /><bar:foo></bar:foo><zoo.bar:baz /><f:format.raw>foobar</f:format.raw>',
                '<zoo:bar /><bar:foo></bar:foo><zoo.bar:baz />',
                '<f:format.raw>foobar</f:format.raw>',
            ],
            'Ignoring namespaces with conflict with registered namespace gives registered namespace priority' => [
                '{namespace f*}{namespace bar}<foo:bar /><bar:foo></bar:foo><foo.bar:baz /><f:format.raw>foobar</f:format.raw>',
                '<foo:bar /><bar:foo></bar:foo><foo.bar:baz />',
                '<f:format.raw>foobar</f:format.raw>',
            ],
        ];
    }

    #[DataProvider('getTemplateCodeFixturesAndExpectations')]
    #[Test]
    public function testTemplateCodeFixture(string $source, string $expectedInOutput, string $notExpectedInOutput): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        self::assertStringContainsString($expectedInOutput, $output);
        self::assertStringNotContainsString($notExpectedInOutput, $output);

        // Second run to verify cached behavior.
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $output = $view->render();
        self::assertStringContainsString($expectedInOutput, $output);
        self::assertStringNotContainsString($notExpectedInOutput, $output);
    }
}
