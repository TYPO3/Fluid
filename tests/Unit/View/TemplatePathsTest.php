<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\View;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;
use TYPO3Fluid\Fluid\View\TemplatePaths;

final class TemplatePathsTest extends TestCase
{
    public static function sanitizePathDataProvider(): array
    {
        return [
            ['', ''],
            ['/foo/bar/baz', '/foo/bar/baz'],
            [['/foo/bar/baz', '/baz'], ['/foo/bar/baz', '/baz']],
            ['C:\\foo\\bar\baz', 'C:/foo/bar/baz'],
            [__FILE__, strtr(__FILE__, '\\', '/')],
            [__DIR__, strtr(__DIR__, '\\', '/') . '/'],
            ['composer.json', strtr(getcwd(), '\\', '/') . '/composer.json'],
            ['php://stdin', 'php://stdin'],
            ['foo://bar/baz', ''],
            ['file://foo/bar/baz', 'file://foo/bar/baz'],
        ];
    }

    #[DataProvider('sanitizePathDataProvider')]
    #[Test]
    public function sanitizePath(string|array $input, string|array $expected): void
    {
        $subject = new TemplatePaths();
        $method = new \ReflectionMethod($subject, 'sanitizePath');
        $output = $method->invoke($subject, $input);
        self::assertSame($expected, $output);
    }

    public static function sanitizePathsDataProvider(): array
    {
        return [
            [['/foo/bar/baz', 'C:\\foo\\bar\\baz'], ['/foo/bar/baz', 'C:/foo/bar/baz']],
            [[__FILE__, __DIR__], [strtr(__FILE__, '\\', '/'), strtr(__DIR__, '\\', '/') . '/']],
            [['', 'composer.json'], ['', strtr(getcwd(), '\\', '/') . '/composer.json']],
        ];
    }

    #[DataProvider('sanitizePathsDataProvider')]
    #[Test]
    public function sanitizePaths(array $input, array $expected): void
    {
        $subject = new TemplatePaths();
        $method = new \ReflectionMethod($subject, 'sanitizePaths');
        $output = $method->invoke($subject, $input);
        self::assertSame($expected, $output);
    }

    #[Test]
    public function getLayoutPathAndFilenameReturnsPreviouslySetLayoutPathAndFilename(): void
    {
        $subject = new TemplatePaths();
        $subject->setLayoutPathAndFilename('/foobar');
        self::assertSame('/foobar', $subject->getLayoutPathAndFilename());
    }

    public static function getGetterAndSetterTestValues(): array
    {
        return [
            ['layoutRootPaths', ['foo' => 'bar']],
            ['templateRootPaths', ['foo' => 'bar']],
            ['partialRootPaths', ['foo' => 'bar']],
        ];
    }

    #[DataProvider('getGetterAndSetterTestValues')]
    #[Test]
    public function testGetterAndSetter(string $property, array $value): void
    {
        $getter = 'get' . ucfirst($property);
        $setter = 'set' . ucfirst($property);
        $subject = $this->getMockBuilder(TemplatePaths::class)->onlyMethods(['sanitizePath'])->getMock();
        $subject->expects(self::any())->method('sanitizePath')->willReturnArgument(0);
        $subject->$setter($value);
        self::assertSame($value, $subject->$getter());
    }

    #[Test]
    public function testResolveAvailableTemplateFiles(): void
    {
        $examplesPath = __DIR__ . '/../../../examples/';
        $instance = new TemplatePaths();
        $instance->setLayoutRootPaths([$examplesPath . 'Resources/Private/Layouts/']);
        $instance->setPartialRootPaths([$examplesPath . 'Resources/Private/Partials/']);
        $instance->setTemplateRootPaths([$examplesPath . 'Resources/Private/Templates/']);
        $instance->setFormat('html');

        self::assertSame(
            [
                $examplesPath . 'Resources/Private/Layouts/Default.fluid.html',
                $examplesPath . 'Resources/Private/Layouts/Dynamic.fluid.html',
            ],
            $this->sortTemplatePaths($instance->resolveAvailableLayoutFiles()),
        );
        self::assertSame(
            [
                $examplesPath . 'Resources/Private/Templates/Default/Default.fluid.html',
                $examplesPath . 'Resources/Private/Templates/Default/Nested/Default.fluid.html',
                $examplesPath . 'Resources/Private/Templates/Other/Default.fluid.html',
                $examplesPath . 'Resources/Private/Templates/Other/List.fluid.html',
            ],
            $this->sortTemplatePaths($instance->resolveAvailableTemplateFiles(null)),
        );
        self::assertSame(
            [
                $examplesPath . 'Resources/Private/Partials/EscapingModifierPartial.fluid.html',
                $examplesPath . 'Resources/Private/Partials/FirstPartial.fluid.html',
                $examplesPath . 'Resources/Private/Partials/SecondPartial.fluid.html',
                $examplesPath . 'Resources/Private/Partials/Structures.fluid.html',
            ],
            $this->sortTemplatePaths($instance->resolveAvailablePartialFiles(null)),
        );
    }

    #[Test]
    public function testGetTemplateSourceThrowsExceptionIfFileNotFound(): void
    {
        $this->expectException(InvalidTemplateResourceException::class);
        $instance = new TemplatePaths();
        $instance->getTemplateSource();
    }

    #[Test]
    public function testGetTemplateSourceReadsStreamWrappers(): void
    {
        $fixture = __DIR__ . '/Fixtures/LayoutFixture.html';
        $instance = new TemplatePaths();
        $stream = fopen($fixture, 'r');
        $instance->setTemplateSource($stream);
        self::assertSame(stream_get_contents($stream), $instance->getTemplateSource());
        fclose($stream);
    }

    #[Test]
    public function testResolveFileInPathsThrowsExceptionIfFileNotFound(): void
    {
        $this->expectException(InvalidTemplateResourceException::class);
        $instance = new TemplatePaths();
        $method = new \ReflectionMethod($instance, 'resolveFileInPaths');
        $method->invoke($instance, ['/not/', '/found/'], 'notfound.html', 'html');
    }

    #[Test]
    public function testGetTemplateIdentifierReturnsSourceChecksumWithControllerAndActionAndFormat(): void
    {
        $instance = new TemplatePaths();
        $instance->setTemplateSource('foobar');
        self::assertSame('source_d78fda63144c5c84_DummyController_dummyAction_html', $instance->getTemplateIdentifier('DummyController', 'dummyAction'));
    }

    public static function getTemplateIdentifierDataProvider(): array
    {
        return [
            [
                __DIR__ . '/Fixtures',
                'ARandomController',
                'TestTemplate',
                'TestTemplate_html_',
            ],
            [
                __DIR__ . '/Fixtures',
                '',
                'UnparsedTemplateFixture',
                'UnparsedTemplateFixture_html_',
            ],
        ];
    }

    #[Test]
    #[DataProvider('getTemplateIdentifierDataProvider')]
    public function getTemplateIdentifier(
        string $templatePath,
        string $controllerName,
        string $actionName,
        string $expectedPrefix,
    ): void {
        $subject = new TemplatePaths();
        $subject->setTemplateRootPaths([$templatePath]);
        $identifier = $subject->getTemplateIdentifier($controllerName, $actionName);
        self::assertStringStartsWith($expectedPrefix, $identifier);
    }

    public static function resolveTemplateFileForControllerAndActionAndFormatDataProvider(): array
    {
        return [
            'with controller and action' => [
                [__DIR__ . '/Fixtures'],
                'ARandomController',
                'TestTemplate',
                __DIR__ . '/Fixtures/ARandomController/TestTemplate.html',
            ],
            'only with action' => [
                [__DIR__ . '/Fixtures'],
                '',
                'UnparsedTemplateFixture',
                __DIR__ . '/Fixtures/UnparsedTemplateFixture.html',
            ],
            'lowercase action' => [
                [__DIR__ . '/Fixtures'],
                '',
                'lowercaseTemplateFixture',
                __DIR__ . '/Fixtures/lowercaseTemplateFixture.html',
            ],
            'action includes format' => [
                [__DIR__ . '/Fixtures'],
                '',
                'UnparsedTemplateFixture.html',
                __DIR__ . '/Fixtures/UnparsedTemplateFixture.html',
            ],
            'action includes path' => [
                [__DIR__ . '/Fixtures'],
                '',
                'ARandomController/Sub/SubTemplate',
                __DIR__ . '/Fixtures/ARandomController/Sub/SubTemplate.html',
            ],
            'controller includes path' => [
                [__DIR__ . '/Fixtures'],
                'ARandomController/Sub',
                'SubTemplate',
                __DIR__ . '/Fixtures/ARandomController/Sub/SubTemplate.html',
            ],
            'controller includes backslash' => [
                [__DIR__ . '/Fixtures'],
                'ARandomController\Sub',
                'SubTemplate',
                __DIR__ . '/Fixtures/ARandomController/Sub/SubTemplate.html',
            ],
            'template exists only in first path' => [
                [__DIR__ . '/Fixtures/TemplateResolving/Templates1', __DIR__ . '/Fixtures/TemplateResolving/Templates2'],
                '',
                'OnlyInFirst',
                __DIR__ . '/Fixtures/TemplateResolving/Templates1/OnlyInFirst.html',
            ],
            'template exists only in second path' => [
                [__DIR__ . '/Fixtures/TemplateResolving/Templates1', __DIR__ . '/Fixtures/TemplateResolving/Templates2'],
                '',
                'OnlyInSecond',
                __DIR__ . '/Fixtures/TemplateResolving/Templates2/OnlyInSecond.html',
            ],
            'template exists in both paths' => [
                [__DIR__ . '/Fixtures/TemplateResolving/Templates1', __DIR__ . '/Fixtures/TemplateResolving/Templates2'],
                '',
                'InBoth',
                __DIR__ . '/Fixtures/TemplateResolving/Templates2/InBoth.html',
            ],
            'non-existent path is skipped' => [
                [__DIR__ . '/Fixtures/TemplateResolving/Templates1', __DIR__ . '/Fixtures/TemplateResolving/TemplatesFoo'],
                '',
                'OnlyInFirst',
                __DIR__ . '/Fixtures/TemplateResolving/Templates1/OnlyInFirst.html',
            ],
            'fluid extension is used' => [
                [__DIR__ . '/Fixtures/TemplateResolving/Templates1'],
                '',
                'FluidExtensionTest',
                __DIR__ . '/Fixtures/TemplateResolving/Templates1/FluidExtensionTest.fluid.html',
            ],
            'non-fluid extension can be used with full name' => [
                [__DIR__ . '/Fixtures/TemplateResolving/Templates1'],
                '',
                'FluidExtensionOverrideTest.html',
                __DIR__ . '/Fixtures/TemplateResolving/Templates1/FluidExtensionOverrideTest.html',
            ],
            'fluid extension is preferred if both exist within one path' => [
                [__DIR__ . '/Fixtures/TemplateResolving/Templates1'],
                '',
                'FluidExtensionOverrideTest',
                __DIR__ . '/Fixtures/TemplateResolving/Templates1/FluidExtensionOverrideTest.fluid.html',
            ],
            // Use case: TYPO3 extension ships .html files, TYPO3 sitepackage overrides with .fluid.html
            'fluid extension overrides non-fluid extension from previous paths' => [
                [__DIR__ . '/Fixtures/TemplateResolving/Templates1', __DIR__ . '/Fixtures/TemplateResolving/Templates2'],
                '',
                'OriginalWithoutFluidExtension',
                __DIR__ . '/Fixtures/TemplateResolving/Templates2/OriginalWithoutFluidExtension.fluid.html',
            ],
            // Use case: TYPO3 core ships .fluid.html, TYPO3 extension overrides with .html
            'non-fluid extension overrides fluid extension from previous paths' => [
                [__DIR__ . '/Fixtures/TemplateResolving/Templates1', __DIR__ . '/Fixtures/TemplateResolving/Templates2'],
                '',
                'OriginalWithFluidExtension',
                __DIR__ . '/Fixtures/TemplateResolving/Templates2/OriginalWithFluidExtension.html',
            ],
        ];
    }

    #[Test]
    #[DataProvider('resolveTemplateFileForControllerAndActionAndFormatDataProvider')]
    public function resolveTemplateFileForControllerAndActionAndFormat(
        array $templatePaths,
        string $controllerName,
        string $actionName,
        string $expectedPath,
    ): void {
        $subject = new TemplatePaths();
        $subject->setTemplateRootPaths($templatePaths);
        // Without runtime cache
        $foundFixture = $subject->resolveTemplateFileForControllerAndActionAndFormat($controllerName, $actionName);
        self::assertSame($expectedPath, $foundFixture);
        // With runtime cache
        $foundFixture = $subject->resolveTemplateFileForControllerAndActionAndFormat($controllerName, $actionName);
        self::assertSame($expectedPath, $foundFixture);
    }

    /**
     * Helper to return paths instead of sorting them in-place
     * @param string[] $paths
     * @return string[]
     */
    private function sortTemplatePaths(array $paths): array
    {
        sort($paths);
        return $paths;
    }
}
