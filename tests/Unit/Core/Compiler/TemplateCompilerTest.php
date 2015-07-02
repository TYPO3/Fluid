<?php
namespace NamelessCoder\Fluid\Tests\Unit\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Cache\SimpleFileCache;
use NamelessCoder\Fluid\Core\Compiler\TemplateCompiler;
use NamelessCoder\Fluid\Core\Parser\ParsingState;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\RootNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\TextNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\NumericNode;
use NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use NamelessCoder\Fluid\Core\Variables\StandardVariableProvider;
use NamelessCoder\Fluid\Core\ViewHelper\ViewHelperResolver;
use NamelessCoder\Fluid\Tests\UnitTestCase;

/**
 * Class TemplateCompilerTest
 */
class TemplateCompilerTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testConstructorCreatesViewHelperResolver() {
		$instance = new TemplateCompiler();
		$this->assertAttributeInstanceOf('NamelessCoder\\Fluid\\Core\\ViewHelper\\ViewHelperResolver', 'viewHelperResolver', $instance);
	}

	/**
	 * @test
	 */
	public function testConstructorAcceptsViewHelperResolver() {
		$resolver = new ViewHelperResolver();
		$instance = new TemplateCompiler($resolver);
		$this->assertAttributeSame($resolver, 'viewHelperResolver', $instance);
	}

	/**
	 * @test
	 */
	public function testSetViewHelperResolverReplacesInstance() {
		$resolver = new ViewHelperResolver();
		$instance = new TemplateCompiler($resolver);
		$instance->setViewHelperResolver(new ViewHelperResolver());
		$this->assertAttributeNotSame($resolver, 'viewHelperResolver', $instance);
	}

	/**
	 * @test
	 */
	public function testSetTemplateCache() {
		$cache = new SimpleFileCache();
		$instance = new TemplateCompiler();
		$instance->setTemplateCache($cache);
		$this->assertAttributeSame($cache, 'templateCache', $instance);
	}

	/**
	 * @test
	 */
	public function testHasReturnsFalseWithoutCache() {
		$instance = $this->getMock('NamelessCoder\\Fluid\\Core\\Compiler\\TemplateCompiler', array('sanitizeIdentifier'));
		$instance->expects($this->never())->method('sanitizeIdentifier');
		$result = $instance->has('test');
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function testHasReturnsFalseAsksCache() {
		$instance = $this->getMock('NamelessCoder\\Fluid\\Core\\Compiler\\TemplateCompiler', array('sanitizeIdentifier'));
		$instance->expects($this->once())->method('sanitizeIdentifier')->with('test')->willReturn('foobar');
		$cache = $this->getMock('NamelessCoder\\Fluid\\Core\\Cache\\SimpleFileCache', array('get'));
		$cache->expects($this->once())->method('get')->with('foobar')->willReturn(TRUE);
		$instance->setTemplateCache($cache);
		$result = $instance->has('test');
		$this->assertTrue($result);
	}

	/**
	 * @test
	 */
	public function testWrapViewHelperNodeArgumentEvaluationInClosure() {
		$instance = new TemplateCompiler();
		$arguments = array('value' => new TextNode('sometext'));
		$viewHelperNode = new ViewHelperNode(new ViewHelperResolver(), 'f', 'format.raw', $arguments, new ParsingState());
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
			'NamelessCoder\\Fluid\\Core\\Compiler\\NodeConverter',
			array('convertListOfSubNodes'),
			array(), '', FALSE
		);
		$nodeConverter->expects($this->at(0))->method('convertListOfSubNodes')->with($foo)->willReturn(array());
		$nodeConverter->expects($this->at(1))->method('convertListOfSubNodes')->with($bar)->willReturn(array());
		$instance = $this->getMock('NamelessCoder\\Fluid\\Core\\Compiler\\TemplateCompiler', array('generateCodeForSection'));
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
	public function testStoreReturnsEarlyIfNoCompilerSet() {
		$instance = $this->getMock('NamelessCoder\\Fluid\\Core\\Compiler\\TemplateCompiler', array('sanitizeIdentifier'));
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
		$this->assertInstanceOf('NamelessCoder\\Fluid\\Core\\Compiler\\NodeConverter', $instance->getNodeConverter());
	}

	/**
	 * @test
	 */
	public function testStoreWhenDisabledFlushesCache() {
		$cache = $this->getMock('NamelessCoder\\Fluid\\Core\\Cache\\SimpleFileCache', array('flush', 'store'));
		$cache->expects($this->never())->method('store');
		$cache->expects($this->once())->method('flush')->with('fakeidentifier');
		$state = new ParsingState();
		$instance = new TemplateCompiler();
		$instance->disable();
		$instance->setTemplateCache($cache);
		$instance->store('fakeidentifier', $state);
	}

}
