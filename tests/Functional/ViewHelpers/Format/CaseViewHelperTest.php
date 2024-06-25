<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers\Format;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class CaseViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderConvertsAValueDataProvider(): array
    {
        return [
            'empty value' => [
                '<f:format.case value="" />',
                '',
            ],
            'value from child, uppercase default' => [
                '<f:format.case>foob4r</f:format.case>',
                'FOOB4R',
            ],
            'simple value' => [
                '<f:format.case value="foo" />',
                'FOO',
            ],
            'mode lower' => [
                '<f:format.case value="FooB4r" mode="lower" />',
                'foob4r',
            ],
            'mode upper' => [
                '<f:format.case value="FooB4r" mode="upper" />',
                'FOOB4R',
            ],
            'mode capital' => [
                '<f:format.case value="foo bar" mode="capital" />',
                'Foo bar',
            ],
            'mode uncapital' => [
                '<f:format.case value="FOO Bar" mode="uncapital" />',
                'fOO Bar',
            ],
            'special chars 1' => [
                '<f:format.case value="smørrebrød" mode="upper" />',
                'SMØRREBRØD',
            ],
            'special chars 2' => [
                '<f:format.case value="smørrebrød" mode="capital" />',
                'Smørrebrød',
            ],
            'special chars 3' => [
                '<f:format.case value="römtömtömtöm" mode="upper" />',
                'RÖMTÖMTÖMTÖM',
            ],
            'special chars 4' => [
                '<f:format.case value="Ἕλλάς α ω" mode="upper" />',
                'ἝΛΛΆΣ Α Ω',
            ],
        ];
    }

    #[DataProvider('renderConvertsAValueDataProvider')]
    #[Test]
    public function renderConvertsAValue(string $template, string $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());
    }

    #[Test]
    public function viewHelperThrowsExceptionIfIncorrectModeIsGiven(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1358349150);
        $view = new TemplateView();
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:format.case value="foo" mode="invalid" />');
        $view->render();
    }
}
