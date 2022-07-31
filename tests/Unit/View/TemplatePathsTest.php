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
    /**
     * @param string|array $input
     * @param string|array $expected
     * @test
     * @dataProvider getSanitizePathTestValues
     */
    public function testSanitizePath($input, $expected)
    {
        $instance = new TemplatePaths();
        $method = new \ReflectionMethod($instance, 'sanitizePath');
        $method->setAccessible(true);
        $output = $method->invokeArgs($instance, [$input]);
        self::assertEquals($expected, $output);
    }

    /**
     * @return array
     */
    public function getSanitizePathTestValues()
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
     * @dataProvider getSanitizePathsTestValues
     */
    public function testSanitizePaths($input, $expected)
    {
        $instance = new TemplatePaths();
        $method = new \ReflectionMethod($instance, 'sanitizePaths');
        $method->setAccessible(true);
        $output = $method->invokeArgs($instance, [$input]);
        self::assertEquals($expected, $output);
    }

    /**
     * @return array
     */
    public function getSanitizePathsTestValues()
    {
        return [
            [['/foo/bar/baz', 'C:\\foo\\bar\\baz'], ['/foo/bar/baz', 'C:/foo/bar/baz']],
            [[__FILE__, __DIR__], [strtr(__FILE__, '\\', '/'), strtr(__DIR__, '\\', '/') . '/']],
            [['', 'composer.json'], ['', strtr(getcwd(), '\\', '/') . '/composer.json']],
        ];
    }

    /**
     * @test
     */
    public function setsLayoutPathAndFilename()
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
    public function setsTemplatePathAndFilename()
    {
        $instance = $this->getMock(TemplatePaths::class, ['sanitizePath']);
        $instance->expects(self::any())->method('sanitizePath')->willReturnArgument(0);
        $instance->setTemplatePathAndFilename('foobar');
        self::assertAttributeEquals('foobar', 'templatePathAndFilename', $instance);
    }

    /**
     * @dataProvider getGetterAndSetterTestValues
     * @param string $property
     * @param mixed $value
     */
    public function testGetterAndSetter($property, $value)
    {
        $getter = 'get' . ucfirst($property);
        $setter = 'set' . ucfirst($property);
        $instance = $this->getMock(TemplatePaths::class, ['sanitizePath']);
        $instance->expects(self::any())->method('sanitizePath')->willReturnArgument(0);
        $instance->$setter($value);
        self::assertEquals($value, $instance->$getter());
    }

    /**
     * @return array
     */
    public function getGetterAndSetterTestValues()
    {
        return [
            ['layoutRootPaths', ['foo' => 'bar']],
            ['templateRootPaths', ['foo' => 'bar']],
            ['partialRootPaths', ['foo' => 'bar']]
        ];
    }

    public function testFillByPackageName()
    {
        $instance = new TemplatePaths('TYPO3Fluid.Fluid');
        self::assertNotEmpty($instance->getTemplateRootPaths());
    }

    public function testFillByConfigurationArray()
    {
        $instance = new TemplatePaths([
            TemplatePaths::CONFIG_TEMPLATEROOTPATHS => ['Resources/Private/Templates/'],
            TemplatePaths::CONFIG_LAYOUTROOTPATHS => ['Resources/Private/Layouts/'],
            TemplatePaths::CONFIG_PARTIALROOTPATHS => ['Resources/Private/Partials/'],
            TemplatePaths::CONFIG_FORMAT => 'xml'
        ]);
        self::assertNotEmpty($instance->getTemplateRootPaths());
    }

    /**
     * @dataProvider getResolveFilesMethodTestValues
     * @param string $method
     */
    public function testResolveFilesMethodCallsResolveFilesInFolders($method, $pathsMethod)
    {
        $instance = $this->getMock(TemplatePaths::class, ['resolveFilesInFolders']);
        $instance->$pathsMethod(['foo']);
        $instance->expects(self::once())->method('resolveFilesInFolders')->with(self::anything(), 'format');
        $instance->$method('format', 'format');
    }

    /**
     * @return array
     */
    public function getResolveFilesMethodTestValues()
    {
        return [
            ['resolveAvailableTemplateFiles', 'setTemplateRootPaths'],
            ['resolveAvailablePartialFiles', 'setPartialRootPaths'],
            ['resolveAvailableLayoutFiles', 'setLayoutRootPaths']
        ];
    }

    public function testToArray()
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
    public function testResolveFilesInFolders()
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
    public function testGetTemplateSourceThrowsExceptionIfFileNotFound()
    {
        $instance = new TemplatePaths();
        $this->setExpectedException(InvalidTemplateResourceException::class);
        $instance->getTemplateSource();
    }

    /**
     * @test
     */
    public function testGetTemplateSourceReadsStreamWrappers()
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
    public function testResolveFileInPathsThrowsExceptionIfFileNotFound()
    {
        $instance = new TemplatePaths();
        $method = new \ReflectionMethod($instance, 'resolveFileInPaths');
        $method->setAccessible(true);
        $this->setExpectedException(InvalidTemplateResourceException::class);
        $method->invokeArgs($instance, [['/not/', '/found/'], 'notfound.html']);
    }

    /**
     * @test
     */
    public function testGetTemplateIdentifierReturnsSourceChecksumWithControllerAndActionAndFormat()
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
