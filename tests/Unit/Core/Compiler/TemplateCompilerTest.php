<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Cache\SimpleFileCache;
use TYPO3\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3\Fluid\Core\Parser\ParsingState;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3\Fluid\Tests\UnitTestCase;

/**
 * Class TemplateCompilerTest
 */
class TemplateCompilerTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testConstructorCreatesViewHelperResolver() {
		$instance = new TemplateCompiler();
		$this->assertAttributeInstanceOf('TYPO3\\Fluid\\Core\\ViewHelper\\ViewHelperResolver', 'viewHelperResolver', $instance);
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
		$instance = $this->getMock('TYPO3\\Fluid\\Core\\Compiler\\TemplateCompiler', array('sanitizeIdentifier'));
		$instance->expects($this->never())->method('sanitizeIdentifier');
		$result = $instance->has('test');
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function testHasReturnsFalseAsksCache() {
		$instance = $this->getMock('TYPO3\\Fluid\\Core\\Compiler\\TemplateCompiler', array('sanitizeIdentifier'));
		$instance->expects($this->once())->method('sanitizeIdentifier')->with('test')->willReturn('foobar');
		$cache = $this->getMock('TYPO3\\Fluid\\Core\\Cache\\SimpleFileCache', array('get'));
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
		$arguments = array('then' => new TextNode('true'));
		$viewHelperNode = new ViewHelperNode(new ViewHelperResolver(), 'f', 'then', $arguments, new ParsingState());
		$result = $instance->wrapViewHelperNodeArgumentEvaluationInClosure($viewHelperNode, 'then');
		$serialized = serialize($arguments['then']);
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
			'TYPO3\\Fluid\\Core\\Compiler\\NodeConverter',
			array('convertListOfSubNodes'),
			array(), '', FALSE
		);
		$nodeConverter->expects($this->at(0))->method('convertListOfSubNodes')->with($foo)->willReturn(array());
		$nodeConverter->expects($this->at(1))->method('convertListOfSubNodes')->with($bar)->willReturn(array());
		$instance = $this->getMock('TYPO3\\Fluid\\Core\\Compiler\\TemplateCompiler', array('generateCodeForSection'));
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
		$instance = $this->getMock('TYPO3\\Fluid\\Core\\Compiler\\TemplateCompiler', array('sanitizeIdentifier'));
		$instance->expects($this->never())->method('sanitizeIdentifier');
		$instance->store('foobar', new ParsingState());
	}

}
