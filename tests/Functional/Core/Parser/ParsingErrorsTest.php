<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\UnknownNamespaceException;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ParsingErrorsTest extends AbstractFunctionalTestCase
{
    public static function getTemplateCodeFixturesAndExpectations(): array
    {
        return [
            'Unclosed ViewHelperNode non-cached' => [
                '<f:section name="Test"></div>',
                Exception::class,
                1238169398,
            ],
            'Unclosed ViewHelperNode cached' => [
                '<f:section name="Test"></div>',
                Exception::class,
                1238169398,
            ],
            'Missing required argument non-cached' => [
                '<f:section></f:section>',
                Exception::class,
                1237823699,
            ],
            'Missing required argument cached' => [
                '<f:section></f:section>',
                Exception::class,
                1237823699,
            ],
            'Argument definition nested in ViewHelper' => [
                '<f:if condition="{true}"><f:argument name="test" type="string" /></f:if>',
                Exception::class,
                1744908510,
            ],
            'Duplicated argument definition' => [
                '<f:argument name="test" type="string" /><f:argument name="test" type="int" />',
                Exception::class,
                1744908509,
            ],
            'Uses invalid namespace non-cached' => [
                '<invalid:section></invalid:section>',
                UnknownNamespaceException::class,
                0,
            ],
            'Uses invalid namespace cached' => [
                '<invalid:section></invalid:section>',
                UnknownNamespaceException::class,
                0,
            ],
        ];
    }

    #[DataProvider('getTemplateCodeFixturesAndExpectations')]
    #[Test]
    public function testTemplateCodeFixture(string $source, string $expectedException, int $expectedExceptionCode): void
    {
        self::expectException($expectedException);
        self::expectExceptionCode($expectedExceptionCode);
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->render();
    }
}
