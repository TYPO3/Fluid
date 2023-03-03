<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class CycleViewHelperTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function renderThrowsExceptionIfSubjectIsNotIterable()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1237823699);
        $value = new \stdClass();
        $view = new TemplateView();
        $view->assignMultiple(['value' => $value]);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:cycle values="{value}" />');
        $view->render();
    }

    public static function renderDataProvider(): \Generator
    {
        $value = ['foo', 'bar', 'baz'];
        yield 'assigns values to array items' => [
            '<f:for each="{0:1, 1:2, 2:3, 3:4}" as="foo"><f:cycle values="{value}" as="cycle">{cycle}</f:cycle></f:for>',
            ['value' => $value],
            'foobarbazfoo',
        ];
        $value = new \ArrayObject();
        yield 'empty object renders only children' => [
            '<f:cycle values="{value}" as="cycle">child node content {cycle}</f:cycle>',
            ['value' => $value],
            'child node content ',
        ];
        $value = [];
        yield 'empty array renders only children' => [
            '<f:cycle values="{value}" as="cycle">child node content {cycle}</f:cycle>',
            ['value' => $value],
            'child node content ',
        ];
    }

    /**
     * @test
     * @dataProvider renderDataProvider
     */
    public function render(string $template, array $variables, string $expected): void
    {
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());
    }
}
