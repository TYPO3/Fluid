<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\Fixtures\PostParseFacetViewHelper;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for TemplateParser.
 *
 * This is to at least half a system test, as it compares rendered results to
 * expectations, and does not strictly check the parsing...
 */
class TemplateParserTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testInitializeViewHelperAndAddItToStackReturnsFalseIfNamespaceNotValid() {
		$resolver = $this->getMock(ViewHelperResolver::class, array('isNamespaceValid'));
		$resolver->expects($this->once())->method('isNamespaceValid')->willReturn(FALSE);
		$templateParser = new TemplateParser($resolver);
		$method = new \ReflectionMethod($templateParser, 'initializeViewHelperAndAddItToStack');
		$method->setAccessible(TRUE);
		$result = $method->invokeArgs($templateParser, array(new ParsingState(), 'f', 'render', array()));
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function testClosingViewHelperTagHandlerReturnsFalseIfNamespaceNotValid() {
		$resolver = $this->getMock(ViewHelperResolver::class, array('isNamespaceValid'));
		$resolver->expects($this->once())->method('isNamespaceValid')->willReturn(FALSE);
		$templateParser = new TemplateParser($resolver);
		$method = new \ReflectionMethod($templateParser, 'closingViewHelperTagHandler');
		$method->setAccessible(TRUE);
		$result = $method->invokeArgs($templateParser, array(new ParsingState(), 'f', 'render'));
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function testBuildObjectTreeThrowsExceptionOnUnclosedViewHelperTag() {
		$templateParser = new TemplateParser();
		$this->setExpectedException(Exception::class);
		$method = new \ReflectionMethod($templateParser, 'buildObjectTree');
		$method->setAccessible(TRUE);
		$method->invokeArgs($templateParser, array(array('<f:render>'), TemplateParser::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS));
	}

	/**
	 * @test
	 */
	public function testParseCallsPreProcessOnTemplateProcessors() {
		$resolver = new ViewHelperResolver();
		$templateParser = new TemplateParser($resolver);
		$processor1 = $this->getMockForAbstractClass(
			TemplateProcessorInterface::class,
			array(), '', FALSE, FALSE, TRUE,
			array('setTemplateParser', 'setViewHelperResolver', 'preProcessSource')
		);
		$processor2 = clone $processor1;
		$processor1->expects($this->once())->method('setTemplateParser')->with($templateParser);
		$processor1->expects($this->once())->method('setViewHelperResolver')->with($resolver);
		$processor1->expects($this->once())->method('preProcessSource')->with('source1')->willReturn('source2');
		$processor2->expects($this->once())->method('setTemplateParser')->with($templateParser);
		$processor2->expects($this->once())->method('setViewHelperResolver')->with($resolver);
		$processor2->expects($this->once())->method('preProcesssource')->with('source2')->willReturn('final');
		$templateParser->setTemplateProcessors(array($processor1, $processor2));
		$templateParser->parse('source1');
	}

	/**
	 * @test
	 * @expectedException Exception
	 */
	public function parseThrowsExceptionWhenStringArgumentMissing() {
		$templateParser = new TemplateParser();
		$templateParser->parse(123);
	}

	/**
	 */
	public function quotedStrings() {
		return array(
			array('"no quotes here"', 'no quotes here'),
			array("'no quotes here'", 'no quotes here'),
			array("'this \"string\" had \\'quotes\\' in it'", 'this "string" had \'quotes\' in it'),
			array('"this \\"string\\" had \'quotes\' in it"', 'this "string" had \'quotes\' in it'),
			array('"a weird \"string\" \'with\' *freaky* \\\\stuff', 'a weird "string" \'with\' *freaky* \\stuff'),
			array('\'\\\'escaped quoted string in string\\\'\'', '\'escaped quoted string in string\'')
		);
	}

	/**
	 * @dataProvider quotedStrings
	 * @test
	 */
	public function unquoteStringReturnsUnquotedStrings($quoted, $unquoted) {
		$templateParser = $this->getAccessibleMock(TemplateParser::class, array('dummy'));
		$this->assertEquals($unquoted, $templateParser->_call('unquoteString', $quoted));
	}

	/**
	 */
	public function templatesToSplit() {
		return array(
			array('TemplateParserTestFixture01-shorthand'),
			array('TemplateParserTestFixture06'),
			array('TemplateParserTestFixture14')
		);
	}

	/**
	 * @dataProvider templatesToSplit
	 * @test
	 */
	public function splitTemplateAtDynamicTagsReturnsCorrectlySplitTemplate($templateName) {
		$template = file_get_contents(__DIR__ . '/Fixtures/' . $templateName . '.html', FILE_TEXT);
		$expectedResult = require(__DIR__ . '/Fixtures/' . $templateName . '-split.php');
		$templateParser = $this->getAccessibleMock(TemplateParser::class, array('dummy'));
		$this->assertSame($expectedResult, $templateParser->_call('splitTemplateAtDynamicTags', $template), 'Filed for ' . $templateName);
	}

	/**
	 * @test
	 */
	public function buildObjectTreeCreatesRootNodeAndSetsUpParsingState() {
		$templateParser = $this->getAccessibleMock(TemplateParser::class, array('dummy'));
		$result = $templateParser->_call('buildObjectTree', array(), TemplateParser::CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS);
		$this->assertInstanceOf(ParsingState::class, $result);
	}

	/**
	 * @test
	 */
	public function buildObjectTreeDelegatesHandlingOfTemplateElements() {
		$templateParser = $this->getAccessibleMock(
			TemplateParser::class,
			array(
				'textHandler',
				'openingViewHelperTagHandler',
				'closingViewHelperTagHandler',
				'textAndShorthandSyntaxHandler'
			)
		);
		$splitTemplate = $templateParser->_call('splitTemplateAtDynamicTags', 'The first part is simple<![CDATA[<f:for each="{a: {a: 0, b: 2, c: 4}}" as="array"><f:for each="{array}" as="value">{value} </f:for>]]><f:format.printf arguments="{number : 362525200}">%.3e</f:format.printf>and here goes some {text} that could have {shorthand}');
		$result = $templateParser->_call('buildObjectTree', $splitTemplate, TemplateParser::CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS);
		$this->assertInstanceOf(ParsingState::class, $result);
	}

	/**
	 * @test
	 */
	public function openingViewHelperTagHandlerDelegatesViewHelperInitialization() {
		$mockState = $this->getMock(ParsingState::class);
		$mockState->expects($this->never())->method('popNodeFromStack');
		$templateParser = $this->getAccessibleMock(
			TemplateParser::class,
			array('parseArguments', 'initializeViewHelperAndAddItToStack')
		);
		$templateParser->expects($this->once())->method('parseArguments')
			->with(array('arguments'))->will($this->returnValue(array('parsedArguments')));
		$templateParser->expects($this->once())->method('initializeViewHelperAndAddItToStack')
			->with($mockState, 'namespaceIdentifier', 'methodIdentifier', array('parsedArguments'));

		$templateParser->_call('openingViewHelperTagHandler', $mockState, 'namespaceIdentifier', 'methodIdentifier', array('arguments'), FALSE, '');
	}

	/**
	 * @test
	 */
	public function openingViewHelperTagHandlerPopsNodeFromStackForSelfClosingTags() {
		$mockState = $this->getMock(ParsingState::class);
		$mockState->expects($this->once())->method('popNodeFromStack')->will($this->returnValue($this->getMock(NodeInterface::class)));
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($this->getMock(NodeInterface::class)));

		$templateParser = $this->getAccessibleMock(
			TemplateParser::class,
			array('parseArguments', 'initializeViewHelperAndAddItToStack')
		);
		$node = $this->getMock(ViewHelperNode::class, array('dummy'), array(), '', FALSE);
		$templateParser->expects($this->once())->method('initializeViewHelperAndAddItToStack')->will($this->returnValue($node));

		$templateParser->_call('openingViewHelperTagHandler', $mockState, '', '', array(), TRUE, '');
	}

	/**
	 * @__test
	 * @expectedException Exception
	 */
	public function initializeViewHelperAndAddItToStackThrowsExceptionIfViewHelperClassDoesNotExisit() {
		$mockState = $this->getMock(ParsingState::class);

		$templateParser = $this->getAccessibleMock(
			TemplateParser::class,
			array(
				'abortIfUnregisteredArgumentsExist',
				'abortIfRequiredArgumentsAreMissing',
				'rewriteBooleanNodesInArgumentsObjectTree'
			)
		);

		$templateParser->_call('initializeViewHelperAndAddItToStack', $mockState, 'f', 'nonExisting', array('arguments'));
	}

	/**
	 * @__test
	 * @expectedException Exception
	 */
	public function initializeViewHelperAndAddItToStackThrowsExceptionIfViewHelperClassNameIsWronglyCased() {
		$mockState = $this->getMock(ParsingState::class);

		$templateParser = $this->getAccessibleMock(
			TemplateParser::class,
			array(
				'abortIfUnregisteredArgumentsExist',
				'abortIfRequiredArgumentsAreMissing',
				'rewriteBooleanNodesInArgumentsObjectTree'
			)
		);

		$templateParser->_call('initializeViewHelperAndAddItToStack', $mockState, 'f', 'wRongLyCased', array('arguments'));
	}

	/**
	 * @__test
	 */
	public function initializeViewHelperAndAddItToStackCreatesRequestedViewHelperAndViewHelperNode() {
		$mockViewHelper = $this->getMock(AbstractViewHelper::class);
		$mockViewHelperNode = $this->getMock(ViewHelperNode::class, array(), array(), '', FALSE);

		$mockState = $this->getMock(ParsingState::class);
		$mockState->expects($this->once())->method('pushNodeToStack')->with($this->anything());

		$templateParser = $this->getAccessibleMock(
			TemplateParser::class,
			array(
				'abortIfUnregisteredArgumentsExist',
				'abortIfRequiredArgumentsAreMissing',
				'rewriteBooleanNodesInArgumentsObjectTree'
			)
		);

		$templateParser->_call('initializeViewHelperAndAddItToStack', $mockState, 'f', 'render', array('arguments'));
	}

	/**
	 * @test
	 * @expectedException Exception
	 */
	public function closingViewHelperTagHandlerThrowsExceptionBecauseOfClosingTagWhichWasNeverOpened() {
		$mockNodeOnStack = $this->getMock(NodeInterface::class, array(), array(), '', FALSE);
		$mockState = $this->getMock(ParsingState::class);
		$mockState->expects($this->once())->method('popNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock(TemplateParser::class, array('dummy'));

		$templateParser->_call('closingViewHelperTagHandler', $mockState, 'f', 'render');
	}

	/**
	 * @test
	 * @expectedException Exception
	 */
	public function closingViewHelperTagHandlerThrowsExceptionBecauseOfWrongTagNesting() {
		$mockNodeOnStack = $this->getMock(ViewHelperNode::class, array(), array(), '', FALSE);
		$mockState = $this->getMock(ParsingState::class);
		$mockState->expects($this->once())->method('popNodeFromStack')->will($this->returnValue($mockNodeOnStack));
		$templateParser = $this->getAccessibleMock(TemplateParser::class, array('dummy'));
		$templateParser->_call('closingViewHelperTagHandler', $mockState, 'f', 'render');
	}

	/**
	 * @test
	 */
	public function objectAccessorHandlerCallsInitializeViewHelperAndAddItToStackIfViewHelperSyntaxIsPresent() {
		$mockState = $this->getMock(ParsingState::class);
		$mockState->expects($this->exactly(2))->method('popNodeFromStack')
			->will($this->returnValue($this->getMock(NodeInterface::class)));
		$mockState->expects($this->exactly(2))->method('getNodeFromStack')
			->will($this->returnValue($this->getMock(NodeInterface::class)));

		$templateParser = $this->getAccessibleMock(
			TemplateParser::class,
			array('recursiveArrayHandler', 'initializeViewHelperAndAddItToStack')
		);
		$templateParser->expects($this->at(0))->method('recursiveArrayHandler')
			->with('format: "H:i"')->will($this->returnValue(array('format' => 'H:i')));
		$templateParser->expects($this->at(1))->method('initializeViewHelperAndAddItToStack')
			->with($mockState, 'f', 'format.date', array('format' => 'H:i'))->will($this->returnValue(TRUE));
		$templateParser->expects($this->at(2))->method('initializeViewHelperAndAddItToStack')
			->with($mockState, 'f', 'debug', array())->will($this->returnValue(TRUE));

		$templateParser->_call('objectAccessorHandler', $mockState, '', '', 'f:debug() -> f:format.date(format: "H:i")', '');
	}

	/**
	 * @test
	 */
	public function objectAccessorHandlerCreatesObjectAccessorNodeWithExpectedValueAndAddsItToStack() {
		$mockNodeOnStack = $this->getMock(AbstractNode::class, array(), array(), '', FALSE);
		$mockNodeOnStack->expects($this->once())->method('addChildNode')->with($this->anything());
		$mockState = $this->getMock(ParsingState::class);
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock(TemplateParser::class, array('dummy'));

		$templateParser->_call('objectAccessorHandler', $mockState, 'objectAccessorString', '', '', '');
	}

	/**
	 * @test
	 */
	public function valuesFromObjectAccessorsAreRunThroughEscapingInterceptorsByDefault() {
		$objectAccessorNodeInterceptor = $this->getMock(InterceptorInterface::class);
		$objectAccessorNodeInterceptor->expects($this->once())->method('process')
			->with($this->anything())->willReturnArgument(0);

		$parserConfiguration = $this->getMock(Configuration::class);
		$parserConfiguration->expects($this->any())->method('getInterceptors')->willReturn(array());
		$parserConfiguration->expects($this->once())->method('getEscapingInterceptors')
			->with(InterceptorInterface::INTERCEPT_OBJECTACCESSOR)
			->will($this->returnValue(array($objectAccessorNodeInterceptor)));

		$mockNodeOnStack = $this->getMock(AbstractNode::class, array(), array(), '', FALSE);
		$mockState = $this->getMock(ParsingState::class);
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock(TemplateParser::class, array('dummy'));
		$templateParser->_set('configuration', $parserConfiguration);

		$templateParser->_call('objectAccessorHandler', $mockState, 'objectAccessorString', '', '', '');
	}

	/**
	 * @test
	 */
	public function valuesFromObjectAccessorsAreNotRunThroughEscapingInterceptorsIfEscapingIsDisabled() {
		$objectAccessorNode = $this->getMock(ObjectAccessorNode::class, array(), array(), '', FALSE);

		$parserConfiguration = $this->getMock(Configuration::class);
		$parserConfiguration->expects($this->any())->method('getInterceptors')->will($this->returnValue(array()));
		$parserConfiguration->expects($this->never())->method('getEscapingInterceptors');

		$mockNodeOnStack = $this->getMock(AbstractNode::class, array(), array(), '', FALSE);
		$mockState = $this->getMock(ParsingState::class);
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock(TemplateParser::class, array('dummy'));
		$templateParser->_set('configuration', $parserConfiguration);
		$templateParser->_set('escapingEnabled', FALSE);

		$templateParser->_call('objectAccessorHandler', $mockState, 'objectAccessorString', '', '', '');
	}


	/**
	 * @test
	 */
	public function valuesFromObjectAccessorsAreRunThroughInterceptors() {
		$objectAccessorNode = $this->getMock(ObjectAccessorNode::class, array(), array(), '', FALSE);
		$objectAccessorNodeInterceptor = $this->getMock(InterceptorInterface::class);
		$objectAccessorNodeInterceptor->expects($this->once())->method('process')
			->with($this->anything())->will($this->returnArgument(0));

		$parserConfiguration = $this->getMock(Configuration::class);
		$parserConfiguration->expects($this->any())->method('getEscapingInterceptors')->willReturn(array());
		$parserConfiguration->expects($this->once())->method('getInterceptors')
			->with(InterceptorInterface::INTERCEPT_OBJECTACCESSOR)->willReturn(array($objectAccessorNodeInterceptor));

		$mockNodeOnStack = $this->getMock(AbstractNode::class, array(), array(), '', FALSE);
		$mockState = $this->getMock(ParsingState::class);
		$mockState->expects($this->once())->method('getNodeFromStack')->willReturn($mockNodeOnStack);

		$templateParser = $this->getAccessibleMock(TemplateParser::class, array('dummy'));
		$templateParser->_set('configuration', $parserConfiguration);
		$templateParser->_set('escapingEnabled', FALSE);

		$templateParser->_call('objectAccessorHandler', $mockState, 'objectAccessorString', '', '', '');
	}

	/**
	 */
	public function argumentsStrings() {
		return array(
			array('a="2"', array('a' => '2')),
			array('a="2" b="foobar \' with \\" quotes"', array('a' => '2', 'b' => 'foobar \' with " quotes')),
			array(' arguments="{number : 362525200}"', array('arguments' => '{number : 362525200}'))
		);
	}

	/**
	 * @test
	 * @dataProvider argumentsStrings
	 * @param string $argumentsString
	 * @param array $expected
	 */
	public function parseArgumentsWorksAsExpected($argumentsString, array $expected) {
		$templateParser = $this->getAccessibleMock(TemplateParser::class, array('buildArgumentObjectTree'));
		$templateParser->expects($this->any())->method('buildArgumentObjectTree')->will($this->returnArgument(0));

		$this->assertSame($expected, $templateParser->_call('parseArguments', $argumentsString));
	}

	/**
	 * @test
	 */
	public function buildArgumentObjectTreeReturnsTextNodeForSimplyString() {

		$templateParser = $this->getAccessibleMock(TemplateParser::class, array('dummy'));

		$this->assertInstanceof(
			TextNode::class,
			$templateParser->_call('buildArgumentObjectTree', 'a very plain string')
		);
	}

	/**
	 * @test
	 */
	public function buildArgumentObjectTreeBuildsObjectTreeForComlexString() {
		$objectTree = $this->getMock(ParsingState::class);
		$objectTree->expects($this->once())->method('getRootNode')->will($this->returnValue('theRootNode'));

		$templateParser = $this->getAccessibleMock(
			TemplateParser::class,
			array('splitTemplateAtDynamicTags', 'buildObjectTree')
		);
		$templateParser->expects($this->at(0))->method('splitTemplateAtDynamicTags')
			->with('a <very> {complex} string')->will($this->returnValue(array('split string')));
		$templateParser->expects($this->at(1))->method('buildObjectTree')
			->with(array('split string'))->will($this->returnValue($objectTree));

		$this->assertEquals('theRootNode', $templateParser->_call('buildArgumentObjectTree', 'a <very> {complex} string'));
	}

	/**
	 * @test
	 */
	public function textAndShorthandSyntaxHandlerDelegatesAppropriately() {
		$mockState = $this->getMock(ParsingState::class, array('getNodeFromStack'));
		$mockState->expects($this->any())->method('getNodeFromStack')->willReturn(new RootNode());

		$templateParser = $this->getMock(
			TemplateParser::class,
			array('objectAccessorHandler', 'arrayHandler', 'textHandler')
		);
		$templateParser->expects($this->at(0))->method('textHandler')->with($mockState, ' ');
		$templateParser->expects($this->at(1))->method('objectAccessorHandler')->with($mockState, 'someThing.absolutely', '', '', '');
		$templateParser->expects($this->at(2))->method('textHandler')->with($mockState, ' "fishy" is \'going\' ');
		$templateParser->expects($this->at(3))->method('arrayHandler')->with($mockState, $this->anything());

		$text = '{1+1} {someThing.absolutely} "fishy" is \'going\' {on: "here"}';
		$method = new \ReflectionMethod(TemplateParser::class, 'textAndShorthandSyntaxHandler');
		$method->setAccessible(TRUE);
		$method->invokeArgs($templateParser, array($mockState, $text, TemplateParser::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS));
	}

	/**
	 * @test
	 */
	public function arrayHandlerAddsArrayNodeWithProperContentToStack() {
		$mockNodeOnStack = $this->getMock(AbstractNode::class, array(), array(), '', FALSE);
		$mockNodeOnStack->expects($this->once())->method('addChildNode')->with($this->anything());
		$mockState = $this->getMock(ParsingState::class);
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock(
			TemplateParser::class,
			array('recursiveArrayHandler')
		);
		$templateParser->expects($this->any())->method('recursiveArrayHandler')
			->with(array('arrayText'))->will($this->returnValue('processedArrayText'));

		$templateParser->_call('arrayHandler', $mockState, array('arrayText'));
	}

	/**
	 */
	public function arrayTexts() {
		return array(
			array(
				'key1: "foo", key2: \'bar\', key3: someVar, key4: 123, key5: { key6: "baz" }',
				array('key1' => 'foo', 'key2' => 'bar', 'key3' => 'someVar', 'key4' => 123.0, 'key5' => array('key6' => 'baz'))
			),
			array(
				'key1: "\'foo\'", key2: \'\\\'bar\\\'\'',
				array('key1' => '\'foo\'', 'key2' => '\'bar\'')
			)
		);
	}

	/**
	 * @__test
	 * @dataProvider arrayTexts
	 */
	public function recursiveArrayHandlerReturnsExpectedArray($arrayText, $expectedArray) {
		$templateParser = $this->getAccessibleMock(TemplateParser::class, array('buildArgumentObjectTree'));
		$templateParser->expects($this->any())->method('buildArgumentObjectTree')->willReturnArgument(0);
		$this->assertSame($expectedArray, $templateParser->_call('recursiveArrayHandler', $arrayText));
	}

	/**
	 * @test
	 */
	public function textNodesAreRunThroughEscapingInterceptorsByDefault() {
		$textNode = $this->getMock(TextNode::class, array(), array(), '', FALSE);
		$textInterceptor = $this->getMock(InterceptorInterface::class);
		$textInterceptor->expects($this->once())->method('process')->with($this->anything())->willReturnArgument(0);

		$parserConfiguration = $this->getMock(Configuration::class);
		$parserConfiguration->expects($this->once())->method('getEscapingInterceptors')
			->with(InterceptorInterface::INTERCEPT_TEXT)->will($this->returnValue(array($textInterceptor)));
		$parserConfiguration->expects($this->any())->method('getInterceptors')->will($this->returnValue(array()));

		$mockNodeOnStack = $this->getMock(AbstractNode::class, array(), array(), '', FALSE);
		$mockNodeOnStack->expects($this->once())->method('addChildNode')->with($this->anything());
		$mockState = $this->getMock(ParsingState::class);
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock(TemplateParser::class, array('splitTemplateAtDynamicTags', 'buildObjectTree'));
		$templateParser->_set('configuration', $parserConfiguration);

		$templateParser->_call('textHandler', $mockState, 'string');
	}

	/**
	 * @test
	 */
	public function textNodesAreNotRunThroughEscapingInterceptorsIfEscapingIsDisabled() {
		$parserConfiguration = $this->getMock(Configuration::class);
		$parserConfiguration->expects($this->never())->method('getEscapingInterceptors');
		$parserConfiguration->expects($this->any())->method('getInterceptors')->will($this->returnValue(array()));

		$mockNodeOnStack = $this->getMock(AbstractNode::class, array(), array(), '', FALSE);
		$mockNodeOnStack->expects($this->once())->method('addChildNode')->with($this->anything());
		$mockState = $this->getMock(ParsingState::class);
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock(
			TemplateParser::class, array('splitTemplateAtDynamicTags', 'buildObjectTree')
		);
		$templateParser->_set('configuration', $parserConfiguration);
		$templateParser->_set('escapingEnabled', FALSE);

		$templateParser->_call('textHandler', $mockState, 'string');
	}

	/**
	 * @test
	 */
	public function textNodesAreRunThroughInterceptors() {
		$textInterceptor = $this->getMock(InterceptorInterface::class);
		$textInterceptor->expects($this->once())->method('process')->with($this->anything())->will($this->returnArgument(0));

		$parserConfiguration = $this->getMock(Configuration::class);
		$parserConfiguration->expects($this->once())->method('getInterceptors')
			->with(InterceptorInterface::INTERCEPT_TEXT)->will($this->returnValue(array($textInterceptor)));
		$parserConfiguration->expects($this->any())->method('getEscapingInterceptors')->will($this->returnValue(array()));

		$mockNodeOnStack = $this->getMock(AbstractNode::class, array(), array(), '', FALSE);
		$mockNodeOnStack->expects($this->once())->method('addChildNode')->with($this->anything());
		$mockState = $this->getMock(ParsingState::class);
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock(
			TemplateParser::class,
			array('splitTemplateAtDynamicTags', 'buildObjectTree')
		);
		$templateParser->_set('configuration', $parserConfiguration);
		$templateParser->_set('escapingEnabled', FALSE);

		$templateParser->_call('textHandler', $mockState, 'string');
	}

	/**
	 * @dataProvider getExampleScriptTestValues
	 * @param string $templateCode
	 * @param array $expectsIgnored
	 * @param string $expectedException
	 */
	public function testNamespaceParsing($templateCode, $expectsIgnored = array(), $expectedException = NULL) {

		$resolver = new ViewHelperResolver();
		$this->templateParser = new TemplateParser($resolver);

		if ($expectedException !== NULL) {
			$this->setExpectedException($expectedException);
		}
		$this->templateParser->parse($templateCode);
		foreach ($expectsIgnored as $namespace) {
			$this->assertTrue($resolver->isNamespaceValidOrIgnored($namespace));
		}
	}

	/**
	 * @return array
	 */
	public function getExampleScriptTestValues() {
		return array(
			array(
				'<f:format.raw></f:format.raw>'
			),
			array(
				'{foo -> f:format.raw()}'
			),
			array(
				'{f:format.raw(value: foo)}'
			),

			array(
				'<foo:bar></foo:bar>',
				array(),
				Exception::class
			),
			array(
				'{foo -> foo:bar()}',
				array(),
				Exception::class
			),
			array(
				'{foo:bar(value: foo)}',
				array(),
				Exception::class
			),

			array(
				'{namespace *} <foo:bar></foo:bar>',
				array('foo')
			),
			array(
				'{namespace foo} {foo -> foo:bar()}',
				array('foo')
			),
			array(
				'{namespace fo*} {foo:bar(value: foo)}',
				array('foo')
			),

			array(
				'<![CDATA[{nonExistingNamespace:basr()}]]>',
				array()
			),

			array(
				'some line
				<![CDATA[{nonExistingNamespace:bar()}]]>
				',
				array()
			),

			array(
				'{namespace before}
				<![CDATA[{nonExistingNamespace:bar()}]]>
				{namespace between}
				<![CDATA[{nonExistingNamespace:baz()}]]>
				{namespace after}',
				array('before', 'between', 'after')
			)
		);
	}
}
