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

final class TrimViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderConvertsAValueDataProvider(): array
    {
        return [
            'empty value' => [
                '<f:format.trim value="" />',
                '',
            ],
            'simple' => [
                '<f:format.trim value="  foo  " />',
                'foo',
            ],
            'trim both' => [
                '<f:format.trim value="  foo  " side="both" />',
                'foo',
            ],
            'trim left' => [
                '<f:format.trim value="  foo  " side="left" />',
                'foo  ',
            ],
            'trim right' => [
                '<f:format.trim value="  foo  " side="right" />',
                '  foo',
            ],
            'trim start' => [
                '<f:format.trim value="  foo  " side="start" />',
                'foo  ',
            ],
            'trim end' => [
                '<f:format.trim value="  foo  " side="end" />',
                '  foo',
            ],
            'simple content' => [
                '<f:format.trim>  foo  </f:format.trim>',
                'foo',
            ],
            'trim content both' => [
                '<f:format.trim side="both">  foo  </f:format.trim>',
                'foo',
            ],
            'trim content left' => [
                '<f:format.trim side="left">  foo  </f:format.trim>',
                'foo  ',
            ],
            'trim content right' => [
                '<f:format.trim side="right">  foo  </f:format.trim>',
                '  foo',
            ],
            'trim content start' => [
                '<f:format.trim side="start">  foo  </f:format.trim>',
                'foo  ',
            ],
            'trim content end' => [
                '<f:format.trim side="end">  foo  </f:format.trim>',
                '  foo',
            ],
            'trim content multiline' => [
                '<f:format.trim>
                    foo
                </f:format.trim>',
                'foo',
            ],
            'trim content characters' => [
                '<f:format.trim characters="bac">abc</f:format.trim>',
                '',
            ],
            'do not trim middle characters' => [
                '<f:format.trim characters="b">abc</f:format.trim>',
                'abc',
            ],
        ];
    }

    #[DataProvider('renderConvertsAValueDataProvider')]
    #[Test]
    public function renderTrimAValue(string $template, $expected): void
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
        $this->expectExceptionCode(1669191560);
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<f:format.trim value="foo" side="invalid" />');
        $view->render();
    }
}
