<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\View;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
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
    #[IgnoreDeprecations]
    public function testFillByPackageName(): void
    {
        $instance = new TemplatePaths('TYPO3Fluid.Fluid');
        self::assertNotEmpty($instance->getTemplateRootPaths());
    }

    #[Test]
    #[IgnoreDeprecations]
    public function testFillByConfigurationArray(): void
    {
        $instance = new TemplatePaths([
            TemplatePaths::CONFIG_TEMPLATEROOTPATHS => ['Resources/Private/Templates/'],
            TemplatePaths::CONFIG_LAYOUTROOTPATHS => ['Resources/Private/Layouts/'],
            TemplatePaths::CONFIG_PARTIALROOTPATHS => ['Resources/Private/Partials/'],
            TemplatePaths::CONFIG_FORMAT => 'xml',
        ]);
        self::assertNotEmpty($instance->getTemplateRootPaths());
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
    #[IgnoreDeprecations]
    public function testToArray(): void
    {
        $subject = $this->getMockBuilder(TemplatePaths::class)->onlyMethods(['sanitizePath'])->getMock();
        $subject->expects(self::any())->method('sanitizePath')->willReturnArgument(0);
        $subject->setTemplateRootPaths(['1']);
        $subject->setLayoutRootPaths(['2']);
        $subject->setPartialRootPaths(['3']);
        $result = $subject->toArray();
        $expected = [
            TemplatePaths::CONFIG_TEMPLATEROOTPATHS => [1],
            TemplatePaths::CONFIG_LAYOUTROOTPATHS => [2],
            TemplatePaths::CONFIG_PARTIALROOTPATHS => [3],
        ];
        self::assertEquals($expected, $result);
    }

    #[Test]
    public function testResolveFilesInFolders(): void
    {
        $subject = new TemplatePaths();
        $method = new \ReflectionMethod($subject, 'resolveFilesInFolders');
        $result = $method->invoke($subject, ['examples/Resources/Private/Layouts/', 'examples/Resources/Private/Templates/Default/'], 'html');
        $expected = [
            'examples/Resources/Private/Layouts/ContentAs.html',
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

    #[Test]
    public function testActionTemplateWithControllerAndAction(): void
    {
        $subject = new TemplatePaths();
        $baseTemplatePath = __DIR__ . '/Fixtures';
        $subject->setTemplateRootPaths([$baseTemplatePath]);
        $foundFixture = $subject->resolveTemplateFileForControllerAndActionAndFormat('ARandomController', 'TestTemplate');
        self::assertSame($baseTemplatePath . '/ARandomController/TestTemplate.html', $foundFixture);
        $identifier = $subject->getTemplateIdentifier('ARandomController', 'TestTemplate');
        self::assertStringStartsWith('ARandomController_action_TestTemplate_', $identifier);
    }

    #[Test]
    public function testActionTemplateWithEmptyController(): void
    {
        $subject = new TemplatePaths();
        $baseTemplatePath = __DIR__ . '/Fixtures';
        $subject->setTemplateRootPaths([$baseTemplatePath]);
        $foundFixture = $subject->resolveTemplateFileForControllerAndActionAndFormat('', 'UnparsedTemplateFixture');
        self::assertSame($baseTemplatePath . '/UnparsedTemplateFixture.html', $foundFixture);
        $identifier = $subject->getTemplateIdentifier('', 'UnparsedTemplateFixture');
        self::assertStringStartsWith('action_UnparsedTemplateFixture_', $identifier);
    }
}
