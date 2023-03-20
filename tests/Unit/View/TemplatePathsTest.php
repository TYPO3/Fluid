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
        $method->setAccessible(true);
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
        $method->setAccessible(true);
        $output = $method->invokeArgs($instance, [$input]);
        self::assertEquals($expected, $output);
    }

    /**
     * @test
     */
    public function setsLayoutPathAndFilename(): void
    {
        $instance = $this->getMock(TemplatePaths::class, ['sanitizePath']);
        $instance->expects(self::any())->method('sanitizePath')->willReturnArgument(0);
        $instance->setLayoutPathAndFilename('foobar');
        self::assertAttributeEquals('foobar', 'layoutPathAndFilename', $instance);
        self::assertEquals('foobar', $instance->getLayoutPathAndFilename());
    }

    /**
     * @test
     */
    public function setsTemplatePathAndFilename(): void
    {
        $instance = $this->getMock(TemplatePaths::class, ['sanitizePath']);
        $instance->expects(self::any())->method('sanitizePath')->willReturnArgument(0);
        $instance->setTemplatePathAndFilename('foobar');
        self::assertAttributeEquals('foobar', 'templatePathAndFilename', $instance);
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
        $instance = $this->getMock(TemplatePaths::class, ['sanitizePath']);
        $instance->expects(self::any())->method('sanitizePath')->willReturnArgument(0);
        $instance->$setter($value);
        self::assertEquals($value, $instance->$getter());
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
        $instance = $this->getMock(TemplatePaths::class, ['resolveFilesInFolders']);
        $instance->$pathsMethod(['foo']);
        $instance->expects(self::once())->method('resolveFilesInFolders')->with(self::anything(), 'format');
        $instance->$method('format', 'format');
    }

    /**
     * @test
     */
    public function testToArray(): void
    {
        $instance = $this->getMock(TemplatePaths::class, ['sanitizePath']);
        $instance->expects(self::any())->method('sanitizePath')->willReturnArgument(0);
        $instance->setTemplateRootPaths(['1']);
        $instance->setLayoutRootPaths(['2']);
        $instance->setPartialRootPaths(['3']);
        $result = $instance->toArray();
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
        $method->setAccessible(true);
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
        $method->setAccessible(true);
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
