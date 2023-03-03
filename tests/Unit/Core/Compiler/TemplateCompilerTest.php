<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\Core\Compiler\NodeConverter;
use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class TemplateCompilerTest
 */
class TemplateCompilerTest extends UnitTestCase
{

    /**
     * @test
     */
    public function testConstructorCreatesNodeConverter()
    {
        $instance = new TemplateCompiler();
        self::assertAttributeInstanceOf(NodeConverter::class, 'nodeConverter', $instance);
    }

    /**
     * @test
     */
    public function testWarmupModeToggle()
    {
        $instance = new TemplateCompiler();
        $instance->enterWarmupMode();
        self::assertAttributeSame(TemplateCompiler::MODE_WARMUP, 'mode', $instance);
        self::assertTrue($instance->isWarmupMode());
    }

    /**
     * @test
     */
    public function testSetRenderingContext()
    {
        $instance = new TemplateCompiler();
        $renderingContext = new RenderingContextFixture();
        $instance->setRenderingContext($renderingContext);
        self::assertAttributeSame($renderingContext, 'renderingContext', $instance);
    }

    /**
     * @test
     */
    public function testHasReturnsFalseWithoutCache()
    {
        $instance = $this->getMock(TemplateCompiler::class, ['sanitizeIdentifier']);
        $renderingContext = $this->getMock(RenderingContextFixture::class, ['getCache']);
        $renderingContext->cacheDisabled = true;
        $renderingContext->expects(self::never())->method('getCache');
        $instance->setRenderingContext($renderingContext);
        $instance->expects(self::once())->method('sanitizeIdentifier')->willReturn('');
        $result = $instance->has('test');
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function testHasAsksCache()
    {
        $cache = $this->getMock(SimpleFileCache::class, ['get']);
        $cache->expects(self::once())->method('get')->with('test')->willReturn(true);
        $renderingContext = new RenderingContextFixture();
        $renderingContext->setCache($cache);
        $instance = $this->getMock(TemplateCompiler::class, ['sanitizeIdentifier']);
        $instance->expects(self::once())->method('sanitizeIdentifier')->willReturnArgument(0);
        $instance->setRenderingContext($renderingContext);
        $result = $instance->has('test');
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function testWrapViewHelperNodeArgumentEvaluationInClosure()
    {
        $instance = new TemplateCompiler();
        $arguments = ['value' => new TextNode('sometext')];
        $renderingContext = new RenderingContextFixture();
        $viewHelperNode = new ViewHelperNode($renderingContext, 'f', 'format.raw', $arguments, new ParsingState());
        $result = $instance->wrapViewHelperNodeArgumentEvaluationInClosure($viewHelperNode, 'value');
        $serialized = serialize($arguments['value']);
        $expected = 'function() use ($renderingContext, $self) {' . chr(10);
        $expected .= chr(10);
        $expected .= 'return \'sometext\';' . chr(10);
        $expected .= '}';
        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function testGenerateSectionCodeFromParsingState()
    {
        $foo = new TextNode('foo');
        $bar = new TextNode('bar');
        $parsingState = new ParsingState();
        $container = new StandardVariableProvider(['1457379500_sections' => [$foo, $bar]]);
        $parsingState->setVariableProvider($container);
        $nodeConverter = $this->getMock(NodeConverter::class, ['convertListOfSubNodes'], [], false, false);
        $nodeConverter->expects(self::exactly(2))->method('convertListOfSubNodes')->willReturnOnConsecutiveCalls(
            [$foo],
            [$bar]
        )->willReturn([]);
        $instance = $this->getMock(TemplateCompiler::class, ['generateCodeForSection']);
        $instance->expects(self::exactly(2))->method('generateCodeForSection')->willReturnOnConsecutiveCalls(
            [self::anything()],
            [self::anything()]
        )->willReturnOnConsecutiveCalls(
            'FOO',
            'BAR'
        );
        $instance->setNodeConverter($nodeConverter);
        $method = new \ReflectionMethod($instance, 'generateSectionCodeFromParsingState');
        $method->setAccessible(true);
        $result = $method->invokeArgs($instance, [$parsingState]);
        self::assertEquals('FOOBAR', $result);
    }

    /**
     * @test
     */
    public function testStoreReturnsEarlyIfDisabled()
    {
        $renderingContext = new RenderingContextFixture();
        $renderingContext->cacheDisabled = true;
        $instance = $this->getMock(TemplateCompiler::class, ['generateSectionCodeFromParsingState']);
        $instance->setRenderingContext($renderingContext);
        $instance->expects(self::never())->method('generateSectionCodeFromParsingState');
        $instance->store('foobar', new ParsingState());
    }

    /**
     * @test
     */
    public function testSupportsDisablingCompiler()
    {
        $instance = new TemplateCompiler();
        $this->setExpectedException(StopCompilingException::class);
        $instance->disable();
    }

    /**
     * @test
     */
    public function testGetNodeConverterReturnsNodeConverterInstance()
    {
        $instance = new TemplateCompiler();
        self::assertInstanceOf(NodeConverter::class, $instance->getNodeConverter());
    }

    /**
     * @test
     */
    public function testStoreSavesUncompilableState()
    {
        $cacheMock = $this->getMockBuilder(SimpleFileCache::class)->onlyMethods(['set'])->getMock();
        $cacheMock->expects(self::once())->method('set')->with('fakeidentifier', self::anything());
        $renderingContext = new RenderingContextFixture();
        $renderingContext->setCache($cacheMock);
        $state = new ParsingState();
        $state->setCompilable(false);
        $instance = new TemplateCompiler();
        $instance->setRenderingContext($renderingContext);
        $instance->store('fakeidentifier', $state);
    }

    /**
     * @test
     */
    public function testVariableNameDelegatesToNodeConverter()
    {
        $instance = new TemplateCompiler();
        $nodeConverter = $this->getMock(NodeConverter::class, ['variableName'], [$instance]);
        $nodeConverter->expects(self::once())->method('variableName')->willReturnArgument(0);
        $instance->setNodeConverter($nodeConverter);
        self::assertEquals('foobar', $instance->variableName('foobar'));
    }

    /**
     * @test
     */
    public function testGetRenderingContextGetsRenderingContext()
    {
        $context = new RenderingContextFixture();
        $instance = new TemplateCompiler();
        $instance->setRenderingContext($context);
        self::assertSame($context, $instance->getRenderingContext());
    }
}
