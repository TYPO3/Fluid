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

    public static function getResolveFilesMethodTestValues(): array
    {
        return [
            ['resolveAvailableTemplateFiles', 'setTemplateRootPaths'],
            ['resolveAvailablePartialFiles', 'setPartialRootPaths'],
            ['resolveAvailableLayoutFiles', 'setLayoutRootPaths'],
        ];
    }

    #[DataProvider('getResolveFilesMethodTestValues')]
    #[Test]
    public function testResolveFilesMethodCallsResolveFilesInFolders(string $method, string $pathsMethod): void
    {
        $subject = $this->getMockBuilder(TemplatePaths::class)->onlyMethods(['resolveFilesInFolders'])->getMock();
        $subject->$pathsMethod(['foo']);
        $subject->expects(self::once())->method('resolveFilesInFolders')->with(self::anything(), 'format');
        $subject->$method('format', 'format');
    }

    #[Test]
    public function testResolveFilesInFolders(): void
    {
        $subject = new TemplatePaths();
        $method = new \ReflectionMethod($subject, 'resolveFilesInFolders');
        $result = $method->invoke($subject, ['examples/Resources/Private/Layouts/', 'examples/Resources/Private/Templates/Default/'], 'html');
        $expected = [
            'examples/Resources/Private/Layouts/Default.html',
            'examples/Resources/Private/Layouts/Dynamic.html',
            'examples/Resources/Private/Templates/Default/Default.html',
            'examples/Resources/Private/Templates/Default/Nested/Default.html',
        ];
        sort($result);
        sort($expected);
        self::assertSame(
            $expected,
            $result,
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
        $method->invoke($instance, ['/not/', '/found/'], 'notfound.html');
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
                'unparsedTemplateFixture',
                __DIR__ . '/Fixtures/UnparsedTemplateFixture.html',
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
}
