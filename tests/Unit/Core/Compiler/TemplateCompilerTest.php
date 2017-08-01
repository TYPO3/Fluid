<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

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
        $this->assertAttributeInstanceOf(NodeConverter::class, 'nodeConverter', $instance);
    }

    /**
     * @test
     */
    public function testWarmupModeToggle()
    {
        $instance = new TemplateCompiler();
        $instance->enterWarmupMode();
        $this->assertAttributeSame(TemplateCompiler::MODE_WARMUP, 'mode', $instance);
        $this->assertTrue($instance->isWarmupMode());
    }

    /**
     * @test
     */
    public function testSetRenderingContext()
    {
        $instance = new TemplateCompiler();
        $renderingContext = new RenderingContextFixture();
        $instance->setRenderingContext($renderingContext);
        $this->assertAttributeSame($renderingContext, 'renderingContext', $instance);
    }

    /**
     * @test
     */
    public function testHasReturnsFalseWithoutCache()
    {
        $instance = $this->getMock(TemplateCompiler::class, ['sanitizeIdentifier']);
        $renderingContext = $this->getMock(RenderingContextFixture::class, ['getCache']);
        $renderingContext->cacheDisabled = true;
        $renderingContext->expects($this->never())->method('getCache');
        $instance->setRenderingContext($renderingContext);
        $result = $instance->has('test');
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function testHasAsksCache()
    {
        $cache = $this->getMock(SimpleFileCache::class, ['get']);
        $cache->expects($this->once())->method('get')->with('test')->willReturn(true);
        $renderingContext = new RenderingContextFixture();
        $renderingContext->setCache($cache);
        $instance = $this->getMock(TemplateCompiler::class, ['sanitizeIdentifier']);
        $instance->expects($this->once())->method('sanitizeIdentifier')->willReturnArgument(0);
        $instance->setRenderingContext($renderingContext);
        $result = $instance->has('test');
        $this->assertTrue($result);
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
        $expected .= sprintf('$argument = unserialize(\'%s\'); return $argument->evaluate($renderingContext);', $serialized);
        $expected .= chr(10) . '}';
        $this->assertEquals($expected, $result);
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
        $nodeConverter = $this->getMock(
            NodeConverter::class,
            ['convertListOfSubNodes'],
            [],
            '',
            false
        );
        $nodeConverter->expects($this->at(0))->method('convertListOfSubNodes')->with($foo)->willReturn([]);
        $nodeConverter->expects($this->at(1))->method('convertListOfSubNodes')->with($bar)->willReturn([]);
        $instance = $this->getMock(TemplateCompiler::class, ['generateCodeForSection']);
        $instance->expects($this->at(0))->method('generateCodeForSection')->with($this->anything())->willReturn('FOO');
        $instance->expects($this->at(1))->method('generateCodeForSection')->with($this->anything())->willReturn('BAR');
        $instance->setNodeConverter($nodeConverter);
        $method = new \ReflectionMethod($instance, 'generateSectionCodeFromParsingState');
        $method->setAccessible(true);
        $result = $method->invokeArgs($instance, [$parsingState]);
        $this->assertEquals('FOOBAR', $result);
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
        $instance->expects($this->never())->method('generateSectionCodeFromParsingState');
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
        $this->assertInstanceOf(NodeConverter::class, $instance->getNodeConverter());
    }

    /**
     * @test
     */
    public function testStoreSavesUncompilableState()
    {
        $cacheMock = $this->getMockBuilder(SimpleFileCache::class)->setMethods(['set'])->getMock();
        $cacheMock->expects($this->once())->method('set')->with('fakeidentifier', $this->anything());
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
        $nodeConverter->expects($this->once())->method('variableName')->willReturnArgument(0);
        $instance->setNodeConverter($nodeConverter);
        $this->assertEquals('foobar', $instance->variableName('foobar'));
    }

    /**
     * @test
     */
    public function testGetRenderingContextGetsRenderingContext()
    {
        $context = new RenderingContextFixture();
        $instance = new TemplateCompiler();
        $instance->setRenderingContext($context);
        $this->assertSame($context, $instance->getRenderingContext());
    }
}
