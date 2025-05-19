<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\ViewHelper\TemplateStructurePlaceholderViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TemplateStructureViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperCollection;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;
use TYPO3Fluid\Fluid\ViewHelpers\ArgumentViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\LayoutViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\SectionViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\SlotViewHelper;

final class TemplateStructureViewHelperResolverTest extends TestCase
{
    public static function irrelevantNamespacesAreIgnoredDataProvider(): array
    {
        return [
            ['f', false],
            ['foo', true],
            ['bar', true],
        ];
    }
    #[Test]
    #[DataProvider('irrelevantNamespacesAreIgnoredDataProvider')]
    public function irrelevantNamespacesAreIgnored(string $namespace, bool $expectedIgnore): void
    {
        $subject = new TemplateStructureViewHelperResolver();
        self::assertSame($expectedIgnore, $subject->isNamespaceIgnored($namespace));
    }

    public static function onlyMainNamespaceIsValidDataProvider(): array
    {
        return [
            ['f', true],
            ['foo', false],
            ['bar', false],
        ];
    }
    #[Test]
    #[DataProvider('onlyMainNamespaceIsValidDataProvider')]
    public function onlyMainNamespaceIsValid(string $namespace, bool $expectedValid): void
    {
        $subject = new TemplateStructureViewHelperResolver();
        self::assertSame($expectedValid, $subject->isNamespaceValid($namespace));
    }

    public static function onlyStructureViewHelpersAreResolvedDataProvider(): array
    {
        return [
            ['f', 'argument', ArgumentViewHelper::class, new ViewHelperCollection('TYPO3Fluid\\Fluid\\ViewHelpers')],
            ['f', 'slot', SlotViewHelper::class, new ViewHelperCollection('TYPO3Fluid\\Fluid\\ViewHelpers')],
            ['f', 'section', SectionViewHelper::class, new ViewHelperCollection('TYPO3Fluid\\Fluid\\ViewHelpers')],
            ['f', 'layout', LayoutViewHelper::class, new ViewHelperCollection('TYPO3Fluid\\Fluid\\ViewHelpers')],
            ['f', 'render', TemplateStructurePlaceholderViewHelper::class, null],
            ['f', 'link.typolink', TemplateStructurePlaceholderViewHelper::class, null],
            ['foo', 'bar', TemplateStructurePlaceholderViewHelper::class, null],
        ];
    }
    #[Test]
    #[DataProvider('onlyStructureViewHelpersAreResolvedDataProvider')]
    public function onlyStructureViewHelpersAreResolved(string $namespace, string $viewHelperName, string $expectedClassName, ?ViewHelperResolverDelegateInterface $expectedDelegate): void
    {
        $subject = new TemplateStructureViewHelperResolver();
        self::assertSame($expectedClassName, $subject->resolveViewHelperClassName($namespace, $viewHelperName));
        self::assertInstanceOf($expectedClassName, $subject->createViewHelperInstance($namespace, $viewHelperName));
        self::assertEquals($expectedDelegate, $subject->getResponsibleDelegate($namespace, $viewHelperName));
    }
}
