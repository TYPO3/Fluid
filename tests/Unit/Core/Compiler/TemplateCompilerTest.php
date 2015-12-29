<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\Core\Compiler\NodeConverter;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Class TemplateCompilerTest
 */
class TemplateCompilerTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testConstructorCreatesNodeConverter() {
		$instance = new TemplateCompiler();
		$this->assertAttributeInstanceOf(NodeConverter::class, 'nodeConverter', $instance);
	}

	/**
	 * @test
	 */
	public function testSetRenderingContext() {
		$instance = new TemplateCompiler();
		$renderingContext = new RenderingContextFixture();
		$instance->setRenderingContext($renderingContext);
		$this->assertAttributeSame($renderingContext, 'renderingContext', $instance);
	}

	/**
	 * @test
	 */
	public function testHasReturnsFalseWithoutCache() {
		$instance = $this->getMock(TemplateCompiler::class, array('sanitizeIdentifier'));
		$renderingContext = new RenderingContextFixture();
		$renderingContext->cacheDisabled = TRUE;
		$instance->setRenderingContext($renderingContext);
		$instance->expects($this->never())->method('sanitizeIdentifier');
		$result = $instance->has('test');
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function testHasAsksCache() {
		$cache = $this->getMock(SimpleFileCache::class, array('get'));
		$cache->expects($this->once())->method('get')->with('test')->willReturn(TRUE);
		$renderingContext = new RenderingContextFixture();
		$renderingContext->setCache($cache);
		$instance = $this->getMock(TemplateCompiler::class, array('sanitizeIdentifier'));
		$instance->expects($this->once())->method('sanitizeIdentifier')->willReturnArgument(0);
		$instance->setRenderingContext($renderingContext);
		$result = $instance->has('test');
		$this->assertTrue($result);
	}

	/**
	 * @test
	 */
	public function testWrapViewHelperNodeArgumentEvaluationInClosure() {
		$instance = new TemplateCompiler();
		$arguments = array('value' => new TextNode('sometext'));
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
	public function testGenerateSectionCodeFromParsingState() {
		$foo = new TextNode('foo');
		$bar = new TextNode('bar');
		$parsingState = new ParsingState();
		$container = new StandardVariableProvider(array('sections' => array($foo, $bar)));
		$parsingState->setVariableProvider($container);
		$nodeConverter = $this->getMock(
			NodeConverter::class,
			array('convertListOfSubNodes'),
			array(), '', FALSE
		);
		$nodeConverter->expects($this->at(0))->method('convertListOfSubNodes')->with($foo)->willReturn(array());
		$nodeConverter->expects($this->at(1))->method('convertListOfSubNodes')->with($bar)->willReturn(array());
		$instance = $this->getMock(TemplateCompiler::class, array('generateCodeForSection'));
		$instance->expects($this->at(0))->method('generateCodeForSection')->with($this->anything())->willReturn('FOO');
		$instance->expects($this->at(1))->method('generateCodeForSection')->with($this->anything())->willReturn('BAR');
		$instance->setNodeConverter($nodeConverter);
		$method = new \ReflectionMethod($instance, 'generateSectionCodeFromParsingState');
		$method->setAccessible(TRUE);
		$result = $method->invokeArgs($instance, array($parsingState));
		$this->assertEquals('FOOBAR', $result);
	}

	/**
	 * @test
	 */
	public function testStoreReturnsEarlyIfDisabled() {
		$renderingContext = new RenderingContextFixture();
		$renderingContext->cacheDisabled = TRUE;
		$instance = $this->getMock(TemplateCompiler::class, array('sanitizeIdentifier'));
		$instance->setRenderingContext($renderingContext);
		$instance->expects($this->never())->method('sanitizeIdentifier');
		$instance->store('foobar', new ParsingState());
	}

	/**
	 * @test
	 */
	public function testSupportsDisablingCompiler() {
		$instance = new TemplateCompiler();
		$instance->disable();
		$this->assertTrue($instance->isDisabled());
	}

	/**
	 * @test
	 */
	public function testGetNodeConverterReturnsNodeConverterInstance() {
		$instance = new TemplateCompiler();
		$this->assertInstanceOf(NodeConverter::class, $instance->getNodeConverter());
	}

	/**
	 * @test
	 */
	public function testStoreWhenDisabledFlushesCache() {
		$renderingContext = new RenderingContextFixture();
		$state = new ParsingState();
		$instance = new TemplateCompiler();
		$instance->setRenderingContext($renderingContext);
		$instance->disable();
		$instance->store('fakeidentifier', $state);
	}

}
