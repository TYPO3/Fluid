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

final class ComponentsTest extends AbstractFunctionalTestCase
{
    public static function renderComponentDataProvider(): iterable
    {
        return [
            ['<my:testComponent title="TITLE" />', 'My test component TITLE'],
            ['<my:slotComponent>SLOT</my:slotComponent>', 'My test component SLOT'],
            ['<my:slotComponent />', 'My test component'],
            ['<my:nested.subComponent />', 'My sub component'],
            ['<my:recursive counter="5" />', '54321'],
            ['<my:additionalVariable />', 'my additional value'],
            ['<my:booleanArgument flag="1 == 1" />', 'true'],
            ['<my:booleanArgument flag="1 == 2" />', 'false'],
        ];
    }

    #[Test]
    #[DataProvider('renderComponentDataProvider')]
    public function renderComponent(string $source, string $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('my', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\TestComponentCollection');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, trim($view->render()));

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('my', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\TestComponentCollection');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, trim($view->render()));
    }
}
