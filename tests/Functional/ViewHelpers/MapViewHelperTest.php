<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3\CMS\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\ArrayAccessExample;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\IterableExample;
use TYPO3Fluid\Fluid\View\TemplateView;

final class MapViewHelperTest extends AbstractFunctionalTestCase
{
    public static function renderValidDataProvider(): iterable
    {
        yield 'empty value' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '<f:map value="{value}" callback="trim" />',
            'expectation' => [],
        ];
        yield 'empty value inline' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:map(callback: \'trim\')}',
            'expectation' => [],
        ];
        yield 'value inline as iterable' => [
            'arguments' => [
                'value' => new IterableExample(['  first  ', "second\t\n\r", '   third']),
            ],
            'src' => '{value -> f:map(callback: \'trim\')}',
            'expectation' => ['first', 'second', 'third'],
        ];
        yield 'viewhelper as callback' => [
            'arguments' => [
                'value' => ['  first  ', "second\t\n\r", '   third'],
            ],
            'src' => '<f:map value="{value}" callback="f:format.trim" />',
            'expectation' => ['first', 'second', 'third'],
        ];
        yield 'viewhelper as callback with arguments' => [
            'arguments' => [
                'value' => ['  first  ', "second\t\n\r", '   third'],
            ],
            'src' => '<f:map value="{value}" callback="f:format.trim" arguments="{side: \'right\'}" />',
            'expectation' => ['  first', 'second', '   third'],
        ];
        yield 'php function as callback with arguments' => [
            'arguments' => [
                'value' => ['  first  ', "second\t\n\r", '   third'],
            ],
            'src' => '<f:map value="{value}" callback="trim" arguments="{characters: \' \'}" />',
            'expectation' => ['first', "second\t\n\r", 'third'],
        ];
    }

    #[DataProvider('renderValidDataProvider')]
    #[Test]
    public function renderValid(array $arguments, string $src, mixed $expectation): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        self::assertSame($expectation, $view->render());

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        self::assertSame($expectation, $view->render());
    }

    public static function renderInvalidDataProvider(): iterable
    {
        yield 'invalid string content' => [
            'arguments' => [
            ],
            'src' => '<f:map callback="trim">SOME TEXT</f:map>',
            'exceptionClass' => \InvalidArgumentException::class,
            'exceptionCode' => 1712224011,
        ];
        yield 'invalid string inline' => [
            'arguments' => [
                'value' => 'string',
            ],
            'src' => '{value -> f:map(callback: \'trim\')}',
            'exceptionClass' => \InvalidArgumentException::class,
            'exceptionCode' => 1712224011,
        ];
        yield 'arrayaccess inline' => [
            'arguments' => [
                'value' => new ArrayAccessExample(['foo' => 'bar']),
            ],
            'src' => '{value -> f:map(callback: \'trim\')}',
            'exceptionClass' => \InvalidArgumentException::class,
            'exceptionCode' => 1712224011,
        ];
        yield 'invalid viewhelper namespace' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:map(callback: \'my:nonexistent.viewhelper\')}',
            'exceptionClass' => \InvalidArgumentException::class,
            'exceptionCode' => 1712224012,
        ];
        yield 'invalid viewhelper' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:map(callback: \'f:nonexistent.viewhelper\')}',
            'exceptionClass' => \TYPO3Fluid\Fluid\Core\Parser\Exception::class,
            'exceptionCode' => 1407060572,
        ];
        yield 'missing required viewhelper arguments' => [
            'arguments' => [
                'value' => ['1,2', '3,4'],
            ],
            'src' => '{value -> f:map(callback: \'f:split\')}',
            'exceptionClass' => \InvalidArgumentException::class,
            'exceptionCode' => 1712224014,
        ];
        yield 'invalid function' => [
            'arguments' => [
                'value' => [],
            ],
            'src' => '{value -> f:map(callback: \'nonexistent_function\')}',
            'exceptionClass' => \InvalidArgumentException::class,
            'exceptionCode' => 1712224013,
        ];
    }

    #[DataProvider('renderInvalidDataProvider')]
    #[Test]
    public function renderInvalid(array $arguments, string $src, string $exceptionClass, int $exceptionCode): void
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionCode($exceptionCode);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        $view->render();

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($src);
        $view->assignMultiple($arguments);
        $view->render();
    }
}
