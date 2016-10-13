<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Cache;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Cache\FluidCacheWarmupResult;
use TYPO3Fluid\Fluid\Core\Cache\StandardCacheWarmer;
use TYPO3Fluid\Fluid\Core\Compiler\FailedCompilingState;
use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
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
        $failedCompilingState = $this->getAccessibleMock(FailedCompilingState::class, ['dummy']);
        $subject = $this->getMockBuilder(StandardCacheWarmer::class)
            ->setMethods(['warmSingleFile', 'detectControllerNamesInTemplateRootPaths'])
            ->getMock();
        $subject->expects($this->exactly(7))
            ->method('detectControllerNamesInTemplateRootPaths')
            ->willReturn(['Default', 'Standard']);
        $subject->expects($this->exactly(70))
            ->method('warmSingleFile')
            ->willReturn($failedCompilingState);
        $context = new RenderingContextFixture();
        $paths = $this->getMockBuilder(TemplatePaths::class)
            ->setMethods(
                [
                    'resolveAvailableTemplateFiles',
                    'resolveAvailablePartialFiles',
                    'resolveAvailableLayoutFiles',
                    'resolveFileInPaths'
                ]
            )
            ->getMock();
        $paths->expects($this->exactly(21))
            ->method('resolveAvailableTemplateFiles')
            ->willReturn(['foo', 'bar']);
        $paths->expects($this->exactly(7))
            ->method('resolveAvailablePartialFiles')
            ->willReturn(['foo', 'bar']);
        $paths->expects($this->exactly(7))
            ->method('resolveAvailableLayoutFiles')
            ->willReturn(['foo', 'bar']);
        $paths->expects($this->exactly(56))->method('resolveFileInPaths')->willReturn('/dev/null');
        $compiler = $this->getMockBuilder(TemplateCompiler::class)
            ->setMethods(['enterWarmupMode'])
            ->getMock();
        $compiler->expects($this->once())->method('enterWarmupMode');
        $context->setTemplateCompiler($compiler);
        $context->setTemplatePaths($paths);
        $failedCompilingState->_set('variableContainer', new StandardVariableProvider());
        $result = $subject->warm($context);
        $this->assertInstanceOf(FluidCacheWarmupResult::class, $result);
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
            $this->assertNotEmpty($resolvedControllerName, 'Generator yielded an empty controller name!');
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
        $parser->expects($this->once())->method('getOrParseAndStoreTemplate')->willThrowException($error);
        $variableProvider = new StandardVariableProvider(['foo' => 'bar']);
        $context->setVariableProvider($variableProvider);
        $context->setTemplateParser($parser);
        $method = new \ReflectionMethod($subject, 'warmSingleFile');
        $method->setAccessible(true);
        $result = $method->invokeArgs($subject, ['/some/file', 'some_file', $context]);
        $this->assertInstanceOf(ParsedTemplateInterface::class, $result);
        $this->assertAttributeNotEmpty('failureReason', $result);
        $this->assertAttributeNotEmpty('mitigations', $result);
    }

    /**
     * @return array
     */
    public function getWarmSingleFileExceptionTestValues()
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
}
