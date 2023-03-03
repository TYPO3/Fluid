<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Cache;

use TYPO3Fluid\Fluid\Core\Cache\FluidCacheWarmupResult;
use TYPO3Fluid\Fluid\Core\Cache\StandardCacheWarmer;
use TYPO3Fluid\Fluid\Core\Compiler\FailedCompilingState;
use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Class StandardCacheWarmerTest
 */
class StandardCacheWarmerTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testWarm()
    {
        $failedCompilingState = $this->getAccessibleMock(FailedCompilingState::class, []);
        $subject = $this->getMockBuilder(StandardCacheWarmer::class)
            ->onlyMethods(['warmSingleFile', 'detectControllerNamesInTemplateRootPaths'])
            ->getMock();
        $subject->expects(self::atLeastOnce())
            ->method('detectControllerNamesInTemplateRootPaths')
            ->willReturn(['Default', 'Standard']);
        $subject->expects(self::atLeastOnce())
            ->method('warmSingleFile')
            ->willReturn($failedCompilingState);
        $context = new RenderingContextFixture();
        $paths = $this->getMockBuilder(TemplatePaths::class)
            ->onlyMethods(
                [
                    'resolveAvailableTemplateFiles',
                    'resolveAvailablePartialFiles',
                    'resolveAvailableLayoutFiles',
                    'resolveFileInPaths',
                    'getTemplateRootPaths',
                    'getPartialRootPaths',
                    'getLayoutRootPaths',
                ]
            )
            ->getMock();
        $paths->expects(self::atLeastOnce())
            ->method('resolveAvailableTemplateFiles')
            ->willReturn(['foo', 'bar']);
        $paths->expects(self::atLeastOnce())
            ->method('resolveAvailablePartialFiles')
            ->willReturn(['foo', 'bar']);
        $paths->expects(self::atLeastOnce())
            ->method('resolveAvailableLayoutFiles')
            ->willReturn(['foo', 'bar']);
        $paths->expects(self::atLeastOnce())->method('resolveFileInPaths')->willReturn('/dev/null');
        $paths->expects(self::atLeastOnce())->method('getTemplateRootPaths')->willReturn(['/dev/null']);
        $paths->expects(self::atLeastOnce())->method('getPartialRootPaths')->willReturn(['/dev/null']);
        $paths->expects(self::atLeastOnce())->method('getLayoutRootPaths')->willReturn(['/dev/null']);
        $compiler = $this->getMockBuilder(TemplateCompiler::class)
            ->onlyMethods(['enterWarmupMode'])
            ->getMock();
        $compiler->expects(self::once())->method('enterWarmupMode');
        $context->setTemplateCompiler($compiler);
        $context->setTemplatePaths($paths);
        $failedCompilingState->_set('variableContainer', new StandardVariableProvider());
        $result = $subject->warm($context);
        self::assertInstanceOf(FluidCacheWarmupResult::class, $result);
    }

    /**
     * @test
     */
    public function testDetectControllerNamesInTemplateRootPaths()
    {
        $subject = new StandardCacheWarmer();
        $method = new \ReflectionMethod($subject, 'detectControllerNamesInTemplateRootPaths');
        $method->setAccessible(true);
        $directory = realpath(__DIR__ . '/../../../../examples/Resources/Private/Templates/');
        $generator = $method->invokeArgs($subject, [[$directory]]);
        foreach ($generator as $resolvedControllerName) {
            self::assertNotEmpty($resolvedControllerName, 'Generator yielded an empty controller name!');
        }
    }

    /**
     * @param \RuntimeException $error
     * @dataProvider getWarmSingleFileExceptionTestValues
     * @test
     */
    public function testWarmuSingleFileHandlesException(\RuntimeException $error)
    {
        $subject = new StandardCacheWarmer();
        $context = new RenderingContextFixture();
        $parser = $this->getMock(TemplateParser::class, ['getOrParseAndStoreTemplate']);
        $parser->expects(self::once())->method('getOrParseAndStoreTemplate')->willThrowException($error);
        $variableProvider = new StandardVariableProvider(['foo' => 'bar']);
        $context->setVariableProvider($variableProvider);
        $context->setTemplateParser($parser);
        $method = new \ReflectionMethod($subject, 'warmSingleFile');
        $method->setAccessible(true);
        $result = $method->invokeArgs($subject, ['/some/file', 'some_file', $context]);
        self::assertInstanceOf(ParsedTemplateInterface::class, $result);
        self::assertAttributeNotEmpty('failureReason', $result);
        self::assertAttributeNotEmpty('mitigations', $result);
    }

    /**
     * @return array
     */
    public static function getWarmSingleFileExceptionTestValues()
    {
        return [
            [new StopCompilingException('StopCompiling exception')],
            [new ExpressionException('Expression exception')],
            [new Exception('Parser exception')],
            [new \TYPO3Fluid\Fluid\Core\ViewHelper\Exception('ViewHelper exception')],
            [new \TYPO3Fluid\Fluid\Core\Exception('Fluid core exception')],
            [new \TYPO3Fluid\Fluid\View\Exception('Fluid view exception')],
            [new \RuntimeException('General runtime exception')]
        ];
    }

    /**
     * @test
     */
    public function testCreateClosureCreatesFileReadingClosure()
    {
        $subject = new StandardCacheWarmer();
        $method = new \ReflectionMethod($subject, 'createClosure');
        $method->setAccessible(true);
        $closure = $method->invokeArgs($subject, [__FILE__]);
        self::assertNotEmpty($closure(new TemplateParser(), new TemplatePaths()));
    }
}
