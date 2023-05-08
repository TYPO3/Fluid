<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\View;

use TYPO3Fluid\Fluid\Tests\BaseTestCase;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;
use TYPO3Fluid\Fluid\View\TemplatePaths;

class TemplatePathsTest extends BaseTestCase
{
    public static function getSanitizePathTestValues(): array
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
            ['file://foo/bar/baz', 'file://foo/bar/baz']
        ];
    }

    /**
     * @param string|array $input
     * @param string|array $expected
     * @test
     * @dataProvider getSanitizePathTestValues
     */
    public function testSanitizePath($input, $expected): void
    {
        $instance = new TemplatePaths();
        $method = new \ReflectionMethod($instance, 'sanitizePath');
        $output = $method->invokeArgs($instance, [$input]);
        self::assertEquals($expected, $output);
    }

    public static function getSanitizePathsTestValues(): array
    {
        return [
            [['/foo/bar/baz', 'C:\\foo\\bar\\baz'], ['/foo/bar/baz', 'C:/foo/bar/baz']],
            [[__FILE__, __DIR__], [strtr(__FILE__, '\\', '/'), strtr(__DIR__, '\\', '/') . '/']],
            [['', 'composer.json'], ['', strtr(getcwd(), '\\', '/') . '/composer.json']],
        ];
    }

    /**
     * @test
     * @dataProvider getSanitizePathsTestValues
     */
    public function testSanitizePaths(array $input, array $expected): void
    {
        $instance = new TemplatePaths();
        $method = new \ReflectionMethod($instance, 'sanitizePaths');
        $output = $method->invokeArgs($instance, [$input]);
        self::assertEquals($expected, $output);
    }

    /**
     * @test
     */
    public function setsLayoutPathAndFilename(): void
    {
        $subject = $this->getMockBuilder(TemplatePaths::class)->onlyMethods(['sanitizePath'])->getMock();
        $subject->expects(self::any())->method('sanitizePath')->willReturnArgument(0);
        $subject->setLayoutPathAndFilename('foobar');
        self::assertAttributeEquals('foobar', 'layoutPathAndFilename', $subject);
        self::assertEquals('foobar', $subject->getLayoutPathAndFilename());
    }

    /**
     * @test
     */
    public function setsTemplatePathAndFilename(): void
    {
        $subject = $this->getMockBuilder(TemplatePaths::class)->onlyMethods(['sanitizePath'])->getMock();
        $subject->expects(self::any())->method('sanitizePath')->willReturnArgument(0);
        $subject->setTemplatePathAndFilename('foobar');
        self::assertAttributeEquals('foobar', 'templatePathAndFilename', $subject);
    }

    public static function getGetterAndSetterTestValues(): array
    {
        return [
            ['layoutRootPaths', ['foo' => 'bar']],
            ['templateRootPaths', ['foo' => 'bar']],
            ['partialRootPaths', ['foo' => 'bar']]
        ];
    }

    /**
     * @dataProvider getGetterAndSetterTestValues
     * @test
     */
    public function testGetterAndSetter(string $property, array $value): void
    {
        $getter = 'get' . ucfirst($property);
        $setter = 'set' . ucfirst($property);
        $subject = $this->getMockBuilder(TemplatePaths::class)->onlyMethods(['sanitizePath'])->getMock();
        $subject->expects(self::any())->method('sanitizePath')->willReturnArgument(0);
        $subject->$setter($value);
        self::assertEquals($value, $subject->$getter());
    }

    /**
     * @test
     */
    public function testFillByPackageName(): void
    {
        $instance = new TemplatePaths('TYPO3Fluid.Fluid');
        self::assertNotEmpty($instance->getTemplateRootPaths());
    }

    /**
     * @test
     */
    public function testFillByConfigurationArray(): void
    {
        $instance = new TemplatePaths([
            TemplatePaths::CONFIG_TEMPLATEROOTPATHS => ['Resources/Private/Templates/'],
            TemplatePaths::CONFIG_LAYOUTROOTPATHS => ['Resources/Private/Layouts/'],
            TemplatePaths::CONFIG_PARTIALROOTPATHS => ['Resources/Private/Partials/'],
            TemplatePaths::CONFIG_FORMAT => 'xml'
        ]);
        self::assertNotEmpty($instance->getTemplateRootPaths());
    }

    public static function getResolveFilesMethodTestValues(): array
    {
        return [
            ['resolveAvailableTemplateFiles', 'setTemplateRootPaths'],
            ['resolveAvailablePartialFiles', 'setPartialRootPaths'],
            ['resolveAvailableLayoutFiles', 'setLayoutRootPaths']
        ];
    }

    /**
     * @dataProvider getResolveFilesMethodTestValues
     * @test
     */
    public function testResolveFilesMethodCallsResolveFilesInFolders(string $method, string $pathsMethod): void
    {
        $subject = $this->getMockBuilder(TemplatePaths::class)->onlyMethods(['resolveFilesInFolders'])->getMock();
        $subject->$pathsMethod(['foo']);
        $subject->expects(self::once())->method('resolveFilesInFolders')->with(self::anything(), 'format');
        $subject->$method('format', 'format');
    }

    /**
     * @test
     */
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
            TemplatePaths::CONFIG_PARTIALROOTPATHS => [3]
        ];
        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function testResolveFilesInFolders(): void
    {
        $instance = new TemplatePaths();
        $method = new \ReflectionMethod($instance, 'resolveFilesInFolders');
        $result = $method->invokeArgs(
            $instance,
            [['examples/Resources/Private/Layouts/', 'examples/Resources/Private/Templates/Default/'], 'html']
        );
        $expected = [
            'examples/Resources/Private/Layouts/Default.html',
            'examples/Resources/Private/Layouts/Dynamic.html',
            'examples/Resources/Private/Templates/Default/Default.html',
            'examples/Resources/Private/Templates/Default/Nested/Default.html',
        ];
        sort($result);
        sort($expected);
        self::assertEquals(
            $expected,
            $result
        );
    }

    /**
     * @test
     */
    public function testGetTemplateSourceThrowsExceptionIfFileNotFound(): void
    {
        $this->expectException(InvalidTemplateResourceException::class);
        $instance = new TemplatePaths();
        $instance->getTemplateSource();
    }

    /**
     * @test
     */
    public function testGetTemplateSourceReadsStreamWrappers(): void
    {
        $fixture = __DIR__ . '/Fixtures/LayoutFixture.html';
        $instance = new TemplatePaths();
        $stream = fopen($fixture, 'r');
        $instance->setTemplateSource($stream);
        self::assertEquals(stream_get_contents($stream), $instance->getTemplateSource());
        fclose($stream);
    }

    /**
     * @test
     */
    public function testResolveFileInPathsThrowsExceptionIfFileNotFound(): void
    {
        $this->expectException(InvalidTemplateResourceException::class);
        $instance = new TemplatePaths();
        $method = new \ReflectionMethod($instance, 'resolveFileInPaths');
        $method->invokeArgs($instance, [['/not/', '/found/'], 'notfound.html']);
    }

    /**
     * @test
     */
    public function testGetTemplateIdentifierReturnsSourceChecksumWithControllerAndActionAndFormat(): void
    {
        $instance = new TemplatePaths();
        $instance->setTemplateSource('foobar');
        self::assertEquals('source_8843d7f92416211de9ebb963ff4ce28125932878_DummyController_dummyAction_html', $instance->getTemplateIdentifier('DummyController', 'dummyAction'));
    }

    /**
     * @test
     */
    public function testActionTemplateWithControllerAndAction(): void
    {
        $subject = new TemplatePaths();
        $baseTemplatePath = __DIR__ . '/Fixtures';
        $subject->setTemplateRootPaths([$baseTemplatePath]);
        $foundFixture = $subject->resolveTemplateFileForControllerAndActionAndFormat('ARandomController', 'TestTemplate');
        self::assertEquals($baseTemplatePath . '/ARandomController/TestTemplate.html', $foundFixture);
        $identifier = $subject->getTemplateIdentifier('ARandomController', 'TestTemplate');
        self::assertStringStartsWith('ARandomController_action_TestTemplate_', $identifier);
    }

    /**
     * @test
     */
    public function testActionTemplateWithEmptyController(): void
    {
        $subject = new TemplatePaths();
        $baseTemplatePath = __DIR__ . '/Fixtures';
        $subject->setTemplateRootPaths([$baseTemplatePath]);
        $foundFixture = $subject->resolveTemplateFileForControllerAndActionAndFormat('', 'UnparsedTemplateFixture');
        self::assertEquals($baseTemplatePath . '/UnparsedTemplateFixture.html', $foundFixture);
        $identifier = $subject->getTemplateIdentifier('', 'UnparsedTemplateFixture');
        self::assertStringStartsWith('action_UnparsedTemplateFixture_', $identifier);
    }
}
