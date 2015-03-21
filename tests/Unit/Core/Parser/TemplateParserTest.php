<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Parser\ParsingState;
use TYPO3\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3\Fluid\Tests\UnitTestCase;
use TYPO3\Fluid\Core\Parser\InterceptorInterface;
use TYPO3\Fluid\Core\Parser\TemplateParser;
use TYPO3\Fluid\Tests\Unit\Core\Parser\Fixtures\PostParseFacetViewHelper;

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
		$resolver = $this->getMock('TYPO3\\Fluid\\Core\\ViewHelper\\ViewHelperResolver', array('isNamespaceValid'));
		$resolver->expects($this->once())->method('isNamespaceValid')->willReturn(FALSE);
		$templateParser = new TemplateParser($resolver);
		$method = new \ReflectionMethod($templateParser, 'initializeViewHelperAndAddItToStack');
		$method->setAccessible(TRUE);
		$result = $method->invokeArgs($templateParser, array(new ParsingState(), 'f', 'render', array()));
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function testClosingViewHelperTagHandlerReturnsFalseIfNamespaceNotValid() {
		$resolver = $this->getMock('TYPO3\\Fluid\\Core\\ViewHelper\\ViewHelperResolver', array('isNamespaceValid'));
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
		$this->setExpectedException('TYPO3\\Fluid\\Core\\Parser\\Exception');
		$method = new \ReflectionMethod($templateParser, 'buildObjectTree');
		$method->setAccessible(TRUE);
		$method->invokeArgs($templateParser, array(array('<f:render>'), TemplateParser::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS));
	}

	/**
	 * @test
	 */
	public function testExtractNamespaceDefinitionsThrowsExceptionIfAttemptingToIgnoreNamespaceWithWildcard() {
		$templateParser = new TemplateParser();
		$this->setExpectedException('TYPO3\\Fluid\\Core\\Parser\\Exception');
		$method = new \ReflectionMethod($templateParser, 'extractNamespaceDefinitions');
		$method->setAccessible(TRUE);
		$method->invokeArgs($templateParser, array('{namespace te*st=Test\\ViewHelpers}'));
	}

	/**
	 * @test
	 * @dataProvider getExtractNamespaceDefinitionsTestValues
	 * @param $source
	 * @param array $expectedNamespaces
	 * @param array $expectedIgnores
	 */
	public function testExtractNamespaceDefinitions($source, array $expectedNamespaces, array $expectedIgnores) {
		$resolver = $this->getMock(
			'TYPO3\\Fluid\\Core\\ViewHelper\\ViewHelperResolver',
			array('registerNamespace', 'ignoreNamespace')
		);
		if (!count($expectedNamespaces)) {
			$resolver->expects($this->never())->method('registerNamespace');
		} else {
			foreach ($expectedNamespaces as $index => $expectedNamespace) {
				$resolver->expects($this->at($index))->method('registerNamespace')->with($expectedNamespace[0], $expectedNamespace[1]);
			}
		}
		if (!count($expectedIgnores)) {
			$resolver->expects($this->never())->method('ignoreNamespace');
		} else {
			foreach ($expectedIgnores as $index => $expectedIgnore) {
				$resolver->expects($this->at($index))->method('ignoreNamespace')->with($expectedIgnore);
			}
		}
		$templateParser = new TemplateParser($resolver);
		$method = new \ReflectionMethod($templateParser, 'extractNamespaceDefinitions');
		$method->setAccessible(TRUE);
		$method->invokeArgs($templateParser, array($source));
	}

	/**
	 * @return array
	 */
	public function getExtractNamespaceDefinitionsTestValues() {
		return array(
			array('', array(), array()),
			array('{namespace php}', array(), array('/^php$/')),
			array('{namespace f=test}', array(), array()),
			array('{namespace test=Test\\ViewHelpers}', array(array('test', 'Test\\ViewHelpers')), array()),
			array('<div xmlns:test="testnamespace"></div>', array(), array()),
			array('<div xmlns:test="http://typo3.org/ns/Foobar/Vh"></div>', array(array('test', 'Foobar\\Vh')), array()),
			array('<div xmlns:f="http://typo3.org/ns/fluid/ViewHelpers"></div>', array(), array()),
		);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\Parser\Exception
	 */
	public function parseThrowsExceptionWhenStringArgumentMissing() {
		$templateParser = new TemplateParser();
		$templateParser->parse(123);
	}

	/**
	 * @__test
	 */
	public function extractNamespaceDefinitionsExtractsNamespacesCorrectly() {
		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
		$templateParser->_call('extractNamespaceDefinitions', ' \{namespace f4=F7\Rocks} {namespace f4=TYPO3\Rocks\Really}');
		$expected = array(
			'f' => 'TYPO3\Fluid\ViewHelpers',
			'f4' => 'TYPO3\Rocks\Really'
		);
		$this->assertEquals($expected, $templateParser->getNamespaces(), 'Namespaces do not match.');
	}

	/**
	 * @__test
	 */
	public function extractNamespaceDefinitionsExtractsXmlNamespacesCorrectly() {
		$mockSettings = array(
			'namespaces' => array(
				'http://domain.tld/ns/my/viewhelpers' => 'My\Namespace',
				'http://otherdomain.tld/ns/other/viewhelpers' => 'My\Other\Namespace'
			),
		);
		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
		$templateParser->injectSettings($mockSettings);
		$templateParser->_call('extractNamespaceDefinitions', 'Some content <html xmlns="http://www.w3.org/1999/xhtml" xmlns:f5="http://domain.tld/ns/my/viewhelpers"
		xmlns:xyz="http://otherdomain.tld/ns/other/viewhelpers" />');
		$expected = array(
			'f' => 'TYPO3\Fluid\ViewHelpers',
			'f5' => 'My\Namespace',
			'xyz' => 'My\Other\Namespace'
		);
		$this->assertEquals($expected, $templateParser->getNamespaces(), 'Namespaces do not match.');
	}

	/**
	 * @__test
	 */
	public function extractNamespaceDefinitionsResolveNamespacesWithDefaultPattern() {
		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
		$templateParser->_call('extractNamespaceDefinitions', '<xml xmlns="http://www.w3.org/1999/xhtml" xmlns:xyz="http://typo3.org/ns/Some/Package/ViewHelpers" />');
		$expected = array(
			'f' => 'TYPO3\Fluid\ViewHelpers',
			'xyz' => 'Some\Package\ViewHelpers'
		);
		$this->assertEquals($expected, $templateParser->getNamespaces(), 'Namespaces do not match.');
	}

	/**
	 * @__test
	 */
	public function extractNamespaceDefinitionsSilentlySkipsXmlNamespaceDeclarationsThatCantBeResolved() {
		$mockSettings = array(
			'namespaces' => array(
				'http://domain.tld/ns/my/viewhelpers' => 'My\Namespace',
			),
		);
		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
		$templateParser->injectSettings($mockSettings);
		$templateParser->_call('extractNamespaceDefinitions', '<xml xmlns="http://www.w3.org/1999/xhtml" xmlns:f5="http://domain.tld/ns/my/viewhelpers"
		xmlns:xyz="http://otherdomain.tld/ns/other/viewhelpers" />');
		$expected = array(
			'f' => 'TYPO3\Fluid\ViewHelpers',
			'f5' => 'My\Namespace'
		);
		$this->assertEquals($expected, $templateParser->getNamespaces(), 'Namespaces do not match.');
	}

	/**
	 * @__test
	 */
	public function extractNamespaceDefinitionsSilentlySkipsXmlNamespaceDeclarationForTheDefaultFluidNamespace() {
		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
		$templateParser->_call('extractNamespaceDefinitions', '<foo xmlns="http://www.w3.org/1999/xhtml" xmlns:f="http://domain.tld/this/will/be/ignored" />');
		$expected = array(
			'f' => 'TYPO3\Fluid\ViewHelpers'
		);
		$this->assertEquals($expected, $templateParser->getNamespaces(), 'Namespaces do not match.');
	}

	/**
	 * @__test
	 * @expectedException \TYPO3\Fluid\Core\Parser\Exception
	 */
	public function extractNamespaceDefinitionsThrowsExceptionIfNamespaceIsRedeclared() {
		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
		$templateParser->_call('extractNamespaceDefinitions', '{namespace typo3=TYPO3\Fluid\Blablubb} {namespace typo3= TYPO3\Rocks\Blu}');
	}

	/**
	 * @__test
	 * @expectedException \TYPO3\Fluid\Core\Parser\Exception
	 */
	public function extractNamespaceDefinitionsThrowsExceptionIfXmlNamespaceIsRedeclaredAsFluidNamespace() {
		$mockSettings = array(
			'namespaces' => array(
				'http://domain.tld/ns/my/viewhelpers' => 'My\Namespace',
			),
		);
		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
		$templateParser->injectSettings($mockSettings);
		$templateParser->_call('extractNamespaceDefinitions', '<foo xmlns="http://www.w3.org/1999/xhtml" xmlns:typo3="http://domain.tld/ns/my/viewhelpers" />{namespace typo3=TYPO3\Fluid\Blablubb}');
	}

	/**
	 * @__test
	 * @expectedException \TYPO3\Fluid\Core\Parser\Exception
	 */
	public function extractNamespaceDefinitionsThrowsExceptionIfFluidNamespaceIsRedeclaredAsXmlNamespace() {
		$mockSettings = array(
			'namespaces' => array(
				'http://domain.tld/ns/my/viewhelpers' => 'My\Namespace',
			),
		);
		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
		$templateParser->injectSettings($mockSettings);
		$templateParser->_call('extractNamespaceDefinitions', '{namespace typo3=TYPO3\Fluid\Blablubb} <foo xmlns="http://www.w3.org/1999/xhtml" xmlns:typo3="http://domain.tld/ns/my/viewhelpers" />');
	}

	/**
	 * @__test
	 * @expectedException \TYPO3\Fluid\Core\Parser\Exception
	 */
	public function registerNamespaceThrowsExceptionIfOneAliasIsRegisteredWithDifferentPhpNamespaces() {
		$templateParser = new TemplateParser();
		$templateParser->registerNamespace('foo', 'Some\Namespace');
		$templateParser->registerNamespace('foo', 'Some\Other\Namespace');
	}

	/**
	 * @__test
	 */
	public function registerNamespaceDoesNotThrowAnExceptionIfTheAliasExistAlreadyAndPointsToTheSamePhpNamespace() {
		$templateParser = new TemplateParser();
		$templateParser->registerNamespace('foo', 'Some\Namespace');
		$templateParser->registerNamespace('foo', 'Some\Namespace');

		// dummy assertion to avoid "risky test" warning
		$this->assertTrue(TRUE);
	}

	/**
	 * @__test
	 */
	public function viewHelperNameWithMultipleLevelsCanBeResolvedByResolveViewHelperName() {
		$mockTemplateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'), array(), '', FALSE);
		$result = $mockTemplateParser->_call('resolveViewHelperName', 'f', 'format.escape');
		$expected = 'TYPO3\Fluid\ViewHelpers\Format\EscapeViewHelper';
		$this->assertEquals($expected, $result, 'The name of the View Helper Name could not be resolved.');
	}

	/**
	 * @__test
	 */
	public function viewHelperNameWithOneLevelCanBeResolvedByResolveViewHelperName() {
		$mockTemplateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'), array(), '', FALSE);
		$actual = $mockTemplateParser->_call('resolveViewHelperName', 'f', 'render');
		$expected = 'TYPO3\Fluid\ViewHelpers\RenderViewHelper';
		$this->assertEquals($expected, $actual);
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
		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
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
		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
		$this->assertSame($expectedResult, $templateParser->_call('splitTemplateAtDynamicTags', $template), 'Filed for ' . $templateName);
	}

	/**
	 * @test
	 */
	public function buildObjectTreeCreatesRootNodeAndSetsUpParsingState() {
		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
		$result = $templateParser->_call('buildObjectTree', array(), TemplateParser::CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS);
		$this->assertInstanceOf('TYPO3\Fluid\Core\Parser\ParsingState', $result);
	}

	/**
	 * @test
	 */
	public function buildObjectTreeDelegatesHandlingOfTemplateElements() {
		$templateParser = $this->getAccessibleMock(
			'TYPO3\Fluid\Core\Parser\TemplateParser',
			array(
				'textHandler',
				'openingViewHelperTagHandler',
				'closingViewHelperTagHandler',
				'textAndShorthandSyntaxHandler'
			)
		);
		$splitTemplate = $templateParser->_call('splitTemplateAtDynamicTags', 'The first part is simple<![CDATA[<f:for each="{a: {a: 0, b: 2, c: 4}}" as="array"><f:for each="{array}" as="value">{value} </f:for>]]><f:format.printf arguments="{number : 362525200}">%.3e</f:format.printf>and here goes some {text} that could have {shorthand}');
		$result = $templateParser->_call('buildObjectTree', $splitTemplate, TemplateParser::CONTEXT_OUTSIDE_VIEWHELPER_ARGUMENTS);
		$this->assertInstanceOf('TYPO3\Fluid\Core\Parser\ParsingState', $result);
	}

	/**
	 * @test
	 */
	public function openingViewHelperTagHandlerDelegatesViewHelperInitialization() {
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$mockState->expects($this->never())->method('popNodeFromStack');
		$templateParser = $this->getAccessibleMock(
			'TYPO3\Fluid\Core\Parser\TemplateParser',
			array('parseArguments', 'initializeViewHelperAndAddItToStack')
		);
		$templateParser->expects($this->once())->method('parseArguments')
			->with(array('arguments'))->will($this->returnValue(array('parsedArguments')));
		$templateParser->expects($this->once())->method('initializeViewHelperAndAddItToStack')
			->with($mockState, 'namespaceIdentifier', 'methodIdentifier', array('parsedArguments'));

		$templateParser->_call('openingViewHelperTagHandler', $mockState, 'namespaceIdentifier', 'methodIdentifier', array('arguments'), FALSE);
	}

	/**
	 * @test
	 */
	public function openingViewHelperTagHandlerPopsNodeFromStackForSelfClosingTags() {
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$mockState->expects($this->once())->method('popNodeFromStack')->will($this->returnValue($this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface')));
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface')));

		$templateParser = $this->getAccessibleMock(
			'TYPO3\Fluid\Core\Parser\TemplateParser',
			array('parseArguments', 'initializeViewHelperAndAddItToStack')
		);
		$templateParser->expects($this->once())->method('initializeViewHelperAndAddItToStack')->will($this->returnValue(TRUE));

		$templateParser->_call('openingViewHelperTagHandler', $mockState, '', '', array(), TRUE);
	}

	/**
	 * @__test
	 * @expectedException \TYPO3\Fluid\Core\Parser\Exception
	 */
	public function initializeViewHelperAndAddItToStackThrowsExceptionIfViewHelperClassDoesNotExisit() {
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');

		$templateParser = $this->getAccessibleMock(
			'TYPO3\Fluid\Core\Parser\TemplateParser',
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
	 * @expectedException \TYPO3\Fluid\Core\Parser\Exception
	 */
	public function initializeViewHelperAndAddItToStackThrowsExceptionIfViewHelperClassNameIsWronglyCased() {
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');

		$templateParser = $this->getAccessibleMock(
			'TYPO3\Fluid\Core\Parser\TemplateParser',
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
		$mockViewHelper = $this->getMock('TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper');
		$mockViewHelperNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array(), array(), '', FALSE);

		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$mockState->expects($this->once())->method('pushNodeToStack')->with($this->anything());

		$templateParser = $this->getAccessibleMock(
			'TYPO3\Fluid\Core\Parser\TemplateParser',
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
	 * @expectedException \TYPO3\Fluid\Core\Parser\Exception
	 */
	public function closingViewHelperTagHandlerThrowsExceptionBecauseOfClosingTagWhichWasNeverOpened() {
		$mockNodeOnStack = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface', array(), array(), '', FALSE);
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$mockState->expects($this->once())->method('popNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));

		$templateParser->_call('closingViewHelperTagHandler', $mockState, 'f', 'render');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Fluid\Core\Parser\Exception
	 */
	public function closingViewHelperTagHandlerThrowsExceptionBecauseOfWrongTagNesting() {
		$mockNodeOnStack = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array(), array(), '', FALSE);
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$mockState->expects($this->once())->method('popNodeFromStack')->will($this->returnValue($mockNodeOnStack));
		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
		$templateParser->_call('closingViewHelperTagHandler', $mockState, 'f', 'render');
	}

	/**
	 * @test
	 */
	public function objectAccessorHandlerCallsInitializeViewHelperAndAddItToStackIfViewHelperSyntaxIsPresent() {
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$mockState->expects($this->exactly(2))->method('popNodeFromStack')
			->will($this->returnValue($this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface')));
		$mockState->expects($this->exactly(2))->method('getNodeFromStack')
			->will($this->returnValue($this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface')));

		$templateParser = $this->getAccessibleMock(
			'TYPO3\Fluid\Core\Parser\TemplateParser',
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
		$mockNodeOnStack = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode', array(), array(), '', FALSE);
		$mockNodeOnStack->expects($this->once())->method('addChildNode')->with($this->anything());
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));

		$templateParser->_call('objectAccessorHandler', $mockState, 'objectAccessorString', '', '', '');
	}

	/**
	 * @test
	 */
	public function valuesFromObjectAccessorsAreRunThroughEscapingInterceptorsByDefault() {
		$objectAccessorNodeInterceptor = $this->getMock('TYPO3\Fluid\Core\Parser\InterceptorInterface');
		$objectAccessorNodeInterceptor->expects($this->once())->method('process')
			->with($this->anything())->willReturnArgument(0);

		$parserConfiguration = $this->getMock('TYPO3\Fluid\Core\Parser\Configuration');
		$parserConfiguration->expects($this->any())->method('getInterceptors')->willReturn(array());
		$parserConfiguration->expects($this->once())->method('getEscapingInterceptors')
			->with(InterceptorInterface::INTERCEPT_OBJECTACCESSOR)
			->will($this->returnValue(array($objectAccessorNodeInterceptor)));

		$mockNodeOnStack = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode', array(), array(), '', FALSE);
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
		$templateParser->_set('configuration', $parserConfiguration);

		$templateParser->_call('objectAccessorHandler', $mockState, 'objectAccessorString', '', '', '');
	}

	/**
	 * @test
	 */
	public function valuesFromObjectAccessorsAreNotRunThroughEscapingInterceptorsIfEscapingIsDisabled() {
		$objectAccessorNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', array(), array(), '', FALSE);

		$parserConfiguration = $this->getMock('TYPO3\Fluid\Core\Parser\Configuration');
		$parserConfiguration->expects($this->any())->method('getInterceptors')->will($this->returnValue(array()));
		$parserConfiguration->expects($this->never())->method('getEscapingInterceptors');

		$mockNodeOnStack = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode', array(), array(), '', FALSE);
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
		$templateParser->_set('configuration', $parserConfiguration);
		$templateParser->_set('escapingEnabled', FALSE);

		$templateParser->_call('objectAccessorHandler', $mockState, 'objectAccessorString', '', '', '');
	}


	/**
	 * @test
	 */
	public function valuesFromObjectAccessorsAreRunThroughInterceptors() {
		$objectAccessorNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode', array(), array(), '', FALSE);
		$objectAccessorNodeInterceptor = $this->getMock('TYPO3\Fluid\Core\Parser\InterceptorInterface');
		$objectAccessorNodeInterceptor->expects($this->once())->method('process')
			->with($this->anything())->will($this->returnArgument(0));

		$parserConfiguration = $this->getMock('TYPO3\Fluid\Core\Parser\Configuration');
		$parserConfiguration->expects($this->any())->method('getEscapingInterceptors')->willReturn(array());
		$parserConfiguration->expects($this->once())->method('getInterceptors')
			->with(InterceptorInterface::INTERCEPT_OBJECTACCESSOR)->willReturn(array($objectAccessorNodeInterceptor));

		$mockNodeOnStack = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode', array(), array(), '', FALSE);
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$mockState->expects($this->once())->method('getNodeFromStack')->willReturn($mockNodeOnStack);

		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));
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
		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('buildArgumentObjectTree'));
		$templateParser->expects($this->any())->method('buildArgumentObjectTree')->will($this->returnArgument(0));

		$this->assertSame($expected, $templateParser->_call('parseArguments', $argumentsString));
	}

	/**
	 * @test
	 */
	public function buildArgumentObjectTreeReturnsTextNodeForSimplyString() {

		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('dummy'));

		$this->assertInstanceof(
			'TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode',
			$templateParser->_call('buildArgumentObjectTree', 'a very plain string')
		);
	}

	/**
	 * @test
	 */
	public function buildArgumentObjectTreeBuildsObjectTreeForComlexString() {
		$objectTree = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$objectTree->expects($this->once())->method('getRootNode')->will($this->returnValue('theRootNode'));

		$templateParser = $this->getAccessibleMock(
			'TYPO3\Fluid\Core\Parser\TemplateParser',
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
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState', array('getNodeFromStack'));
		$mockState->expects($this->any())->method('getNodeFromStack')->willReturn(new RootNode());

		$templateParser = $this->getMock(
			'TYPO3\Fluid\Core\Parser\TemplateParser',
			array('objectAccessorHandler', 'arrayHandler', 'textHandler')
		);
		$templateParser->expects($this->at(0))->method('textHandler')->with($mockState, ' ');
		$templateParser->expects($this->at(1))->method('objectAccessorHandler')->with($mockState, 'someThing.absolutely', '', '', '');
		$templateParser->expects($this->at(2))->method('textHandler')->with($mockState, ' "fishy" is \'going\' ');
		$templateParser->expects($this->at(3))->method('arrayHandler')->with($mockState, $this->anything());

		$text = '{1+1} {someThing.absolutely} "fishy" is \'going\' {on: "here"}';
		$method = new \ReflectionMethod('TYPO3\Fluid\Core\Parser\TemplateParser', 'textAndShorthandSyntaxHandler');
		$method->setAccessible(TRUE);
		$method->invokeArgs($templateParser, array($mockState, $text, TemplateParser::CONTEXT_INSIDE_VIEWHELPER_ARGUMENTS));
	}

	/**
	 * @test
	 */
	public function arrayHandlerAddsArrayNodeWithProperContentToStack() {
		$mockNodeOnStack = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode', array(), array(), '', FALSE);
		$mockNodeOnStack->expects($this->once())->method('addChildNode')->with($this->anything());
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock(
			'TYPO3\Fluid\Core\Parser\TemplateParser',
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
		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('buildArgumentObjectTree'));
		$templateParser->expects($this->any())->method('buildArgumentObjectTree')->willReturnArgument(0);
		$this->assertSame($expectedArray, $templateParser->_call('recursiveArrayHandler', $arrayText));
	}

	/**
	 * @test
	 */
	public function textNodesAreRunThroughEscapingInterceptorsByDefault() {
		$textNode = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode', array(), array(), '', FALSE);
		$textInterceptor = $this->getMock('TYPO3\Fluid\Core\Parser\InterceptorInterface');
		$textInterceptor->expects($this->once())->method('process')->with($this->anything())->willReturnArgument(0);

		$parserConfiguration = $this->getMock('TYPO3\Fluid\Core\Parser\Configuration');
		$parserConfiguration->expects($this->once())->method('getEscapingInterceptors')
			->with(InterceptorInterface::INTERCEPT_TEXT)->will($this->returnValue(array($textInterceptor)));
		$parserConfiguration->expects($this->any())->method('getInterceptors')->will($this->returnValue(array()));

		$mockNodeOnStack = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode', array(), array(), '', FALSE);
		$mockNodeOnStack->expects($this->once())->method('addChildNode')->with($this->anything());
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock('TYPO3\Fluid\Core\Parser\TemplateParser', array('splitTemplateAtDynamicTags', 'buildObjectTree'));
		$templateParser->_set('configuration', $parserConfiguration);

		$templateParser->_call('textHandler', $mockState, 'string');
	}

	/**
	 * @test
	 */
	public function textNodesAreNotRunThroughEscapingInterceptorsIfEscapingIsDisabled() {
		$parserConfiguration = $this->getMock('TYPO3\Fluid\Core\Parser\Configuration');
		$parserConfiguration->expects($this->never())->method('getEscapingInterceptors');
		$parserConfiguration->expects($this->any())->method('getInterceptors')->will($this->returnValue(array()));

		$mockNodeOnStack = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode', array(), array(), '', FALSE);
		$mockNodeOnStack->expects($this->once())->method('addChildNode')->with($this->anything());
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock(
			'TYPO3\Fluid\Core\Parser\TemplateParser', array('splitTemplateAtDynamicTags', 'buildObjectTree')
		);
		$templateParser->_set('configuration', $parserConfiguration);
		$templateParser->_set('escapingEnabled', FALSE);

		$templateParser->_call('textHandler', $mockState, 'string');
	}

	/**
	 * @test
	 */
	public function textNodesAreRunThroughInterceptors() {
		$textInterceptor = $this->getMock('TYPO3\Fluid\Core\Parser\InterceptorInterface');
		$textInterceptor->expects($this->once())->method('process')->with($this->anything())->will($this->returnArgument(0));

		$parserConfiguration = $this->getMock('TYPO3\Fluid\Core\Parser\Configuration');
		$parserConfiguration->expects($this->once())->method('getInterceptors')
			->with(InterceptorInterface::INTERCEPT_TEXT)->will($this->returnValue(array($textInterceptor)));
		$parserConfiguration->expects($this->any())->method('getEscapingInterceptors')->will($this->returnValue(array()));

		$mockNodeOnStack = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode', array(), array(), '', FALSE);
		$mockNodeOnStack->expects($this->once())->method('addChildNode')->with($this->anything());
		$mockState = $this->getMock('TYPO3\Fluid\Core\Parser\ParsingState');
		$mockState->expects($this->once())->method('getNodeFromStack')->will($this->returnValue($mockNodeOnStack));

		$templateParser = $this->getAccessibleMock(
			'TYPO3\Fluid\Core\Parser\TemplateParser',
			array('splitTemplateAtDynamicTags', 'buildObjectTree')
		);
		$templateParser->_set('configuration', $parserConfiguration);
		$templateParser->_set('escapingEnabled', FALSE);

		$templateParser->_call('textHandler', $mockState, 'string');
	}
}
