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
            ],
            'Unclosed ViewHelperNode cached' => [
                '<f:section name="Test"></div>',
                Exception::class,
            ],
            'Missing required argument non-cached' => [
                '<f:section></f:section>',
                Exception::class,
            ],
            'Missing required argument cached' => [
                '<f:section></f:section>',
                Exception::class,
            ],
            'Uses invalid namespace non-cached' => [
                '<invalid:section></invalid:section>',
                UnknownNamespaceException::class,
            ],
            'Uses invalid namespace cached' => [
                '<invalid:section></invalid:section>',
                UnknownNamespaceException::class,
            ],
        ];
    }

    #[DataProvider('getTemplateCodeFixturesAndExpectations')]
    #[Test]
    public function testTemplateCodeFixture(string $source, string $expectedException): void
    {
        $this->expectException($expectedException);
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->render();
    }
}
