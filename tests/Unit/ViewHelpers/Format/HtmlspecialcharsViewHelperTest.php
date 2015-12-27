<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithoutToString;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3Fluid\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper;

/**
 * Test for \TYPO3Fluid\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper
 */
class HtmlspecialcharsViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var HtmlspecialcharsViewHelper|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getMock(HtmlspecialcharsViewHelper::class, array('renderChildren'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
	}

	/**
	 * @test
	 */
	public function viewHelperDeactivatesEscapingInterceptor() {
		$this->assertFalse($this->viewHelper->isOutputEscapingEnabled());
	}

	/**
	 * @test
	 */
	public function renderUsesValueAsSourceIfSpecified() {
		$this->viewHelper->expects($this->never())->method('renderChildren');
		$this->viewHelper->setArguments(
			array('value' => 'Some string', 'keepQuotes' => FALSE, 'encoding' => 'UTF-8', 'doubleEncode' => FALSE)
		);
		$actualResult = $this->viewHelper->initializeArgumentsAndRender();
		$this->assertEquals('Some string', $actualResult);
	}

	/**
	 * test
	 */
	public function renderUsesChildNodesAsSourceIfSpecified() {
		$this->viewHelper->expects($this->atLeastOnce())->method('renderChildren')->will($this->returnValue('Some string'));
		$actualResult = $this->viewHelper->initializeArgumentsAndRender();
		$this->assertEquals('Some string', $actualResult);
	}

	public function dataProvider() {
		return array(
			// render does not modify string without special characters
			array(
				'value' => 'This is a sample text without special characters.',
				'options' => array(),
				'expectedResult' => 'This is a sample text without special characters.'
			),
			// render decodes simple string
			array(
				'value' => 'Some special characters: &©"\'',
				'options' => array(),
				'expectedResult' => 'Some special characters: &amp;©&quot;&#039;'
			),
			// render respects "keepQuotes" argument
			array(
				'value' => 'Some special characters: &©"',
				'options' => array(
					'keepQuotes' => TRUE,
				),
				'expectedResult' => 'Some special characters: &amp;©"'
			),
			// render respects "encoding" argument
			array(
				'value' => utf8_decode('Some special characters: &"\''),
				'options' => array(
					'encoding' => 'ISO-8859-1',
				),
				'expectedResult' => 'Some special characters: &amp;&quot;&#039;'
			),
			// render converts already converted entities by default
			array(
				'value' => 'already &quot;encoded&quot;',
				'options' => array(),
				'expectedResult' => 'already &amp;quot;encoded&amp;quot;'
			),
			// render does not convert already converted entities if "doubleEncode" is FALSE
			array(
				'value' => 'already &quot;encoded&quot;',
				'options' => array(
					'doubleEncode' => FALSE,
				),
				'expectedResult' => 'already &quot;encoded&quot;'
			),
			// render returns unmodified source if it is a float
			array(
				'value' => 123.45,
				'options' => array(),
				'expectedResult' => 123.45
			),
			// render returns unmodified source if it is an integer
			array(
				'value' => 12345,
				'options' => array(),
				'expectedResult' => 12345
			),
			// render returns unmodified source if it is a boolean
			array(
				'value' => TRUE,
				'options' => array(),
				'expectedResult' => TRUE
			),
		);
	}

	/**
	 * test
	 *
	 * @dataProvider dataProvider
	 */
	public function renderTests($value, array $options, $expectedResult) {
		$options['value'] = $value;
		$this->viewHelper->setArguments($options);
		$this->assertSame($expectedResult, $this->viewHelper->initializeArgumentsAndRender());
	}

	/**
	 * @test
	 * @dataProvider dataProvider
	 */
	public function renderTestsWithRenderChildrenFallback($value, array $options, $expectedResult) {
		$this->viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue($value));
		$options['value'] = NULL;
		$options['keepQuotes'] = (boolean) (isset($options['keepQuotes']) && $options['keepQuotes'] ? $options['keepQuotes'] : FALSE);
		$options['encoding'] = 'UTF-8';
		$options['doubleEncode'] = (boolean) (isset($options['doubleEncode']) ? $options['doubleEncode'] : TRUE);
		$this->viewHelper->setArguments($options);
		$this->assertSame($expectedResult, $this->viewHelper->initializeArgumentsAndRender());
	}

	/**
	 * __test
	 *
	 * @dataProvider dataProvider
	 */
	public function compileTests($value, array $options, $expectedResult) {
		/** @var ViewHelperNode|\PHPUnit_Framework_MockObject_MockObject $mockSyntaxTreeNode */
		$mockSyntaxTreeNode = $this->getMockBuilder(ViewHelperNode::class)->disableOriginalConstructor()->getMock();

		/** @var TemplateCompiler|\PHPUnit_Framework_MockObject_MockObject $mockTemplateCompiler */
		$mockTemplateCompiler = $this->getMockBuilder(TemplateCompiler::class)->disableOriginalConstructor()->getMock();
		$mockTemplateCompiler->expects($this->once())->method('variableName')->with('value')->will($this->returnValue('$value123'));

		$arguments = array(
			'value' => $value,
			'keepQuotes' => (boolean) (isset($options['keepQuotes']) && $options['keepQuotes'] ? $options['keepQuotes'] : FALSE),
			'encoding' => 'UTF-8',
			'doubleEncode' => (boolean) (isset($options['doubleEncode']) ? $options['doubleEncode'] : TRUE),
		);
		$arguments = array_merge($arguments, $options);
		$initializationPhpCode = '$arguments = ' . var_export($arguments, TRUE) . ';' . chr(10);
		$compiledPhpCode = $this->viewHelper->compile('$arguments', 'NULL', $initializationPhpCode, $mockSyntaxTreeNode, $mockTemplateCompiler);
		$result = NULL;
		eval($initializationPhpCode . '$result = ' . $compiledPhpCode . ';');
		$this->assertSame($expectedResult, $result);
	}

	/**
	 * __test
	 *
	 * @dataProvider dataProvider
	 */
	public function compileTestsWithRenderChildrenFallback($value, array $options, $expectedResult) {
		/** @var ViewHelperNode|\PHPUnit_Framework_MockObject_MockObject $mockSyntaxTreeNode */
		$mockSyntaxTreeNode = $this->getMockBuilder(ViewHelperNode::class)->disableOriginalConstructor()->getMock();

		/** @var TemplateCompiler|\PHPUnit_Framework_MockObject_MockObject $mockTemplateCompiler */
		$mockTemplateCompiler = $this->getMockBuilder(TemplateCompiler::class)->disableOriginalConstructor()->getMock();
		$mockTemplateCompiler->expects($this->once())->method('variableName')->with('value')->will($this->returnValue('$value123'));

		$renderChildrenClosureName = uniqid('renderChildren');
		$arguments = array(
			'value' => NULL,
			'keepQuotes' => (boolean) isset($options['keepQuotes']) && $options['keepQuotes'],
			'encoding' => isset($options['keepQuotes']) ? $options['keepQuotes'] : 'UTF-8',
			'doubleEncode' => (boolean) isset($options['doubleEncode']) && $options['doubleEncode'],
		);
		$arguments = array_merge($arguments, $options);
		$initializationPhpCode = 'function ' . $renderChildrenClosureName . '() { return ' . var_export($value, TRUE) . '; }; ' . chr(10) . '$arguments = ' . var_export($arguments, TRUE) . ';' . chr(10);
		$compiledPhpCode = $this->viewHelper->compile('$arguments', $renderChildrenClosureName, $initializationPhpCode, $mockSyntaxTreeNode, $mockTemplateCompiler);
		$result = NULL;
		eval($initializationPhpCode . '$result = ' . $compiledPhpCode . ';');
		$this->assertSame($expectedResult, $result);
	}

	/**
	 * __test
	 */
	public function renderConvertsObjectsToStrings() {
		$user = new UserWithToString('Xaver <b>Cross-Site</b>');
		$expectedResult = 'Xaver &lt;b&gt;Cross-Site&lt;/b&gt;';
		$this->viewHelper->setArguments(array('value' => $user));
		$actualResult = $this->viewHelper->initializeArgumentsAndRender();
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * __test
	 */
	public function renderDoesNotModifySourceIfItIsAnObjectThatCantBeConvertedToAString() {
		$user = new UserWithoutToString('Xaver <b>Cross-Site</b>');
		$this->viewHelper->setArguments(array('value' => $user));
		$actualResult = $this->viewHelper->initializeArgumentsAndRender();
		$this->assertSame($user, $actualResult);
	}

	/**
	 * __test
	 */
	public function compileConvertsObjectsToStrings() {
		/** @var AbstractNode|\PHPUnit_Framework_MockObject_MockObject $mockSyntaxTreeNode */
		$mockSyntaxTreeNode = $this->getMockBuilder(AbstractNode::class)->disableOriginalConstructor()->getMock();

		/** @var TemplateCompiler|\PHPUnit_Framework_MockObject_MockObject $mockTemplateCompiler */
		$mockTemplateCompiler = $this->getMockBuilder(TemplateCompiler::class)->disableOriginalConstructor()->getMock();
		$mockTemplateCompiler->expects($this->once())->method('variableName')->with('value')->will($this->returnValue('$value123'));

		$initializationPhpCode = '$arguments = array("value" => new \TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString("Xaver <b>Cross-Site</b>"), "keepQuotes" => FALSE, "encoding" => "UTF-8", "doubleEncode" => TRUE);' . chr(10);
		$compiledPhpCode = $this->viewHelper->compile('$arguments', 'NULL', $initializationPhpCode, $mockSyntaxTreeNode, $mockTemplateCompiler);
		$result = NULL;
		eval($initializationPhpCode . '$result = ' . $compiledPhpCode . ';');
		$this->assertSame('Xaver &lt;b&gt;Cross-Site&lt;/b&gt;', $result);
	}

	/**
	 * @test
	 */
	public function compileDoesNotModifySourceIfItIsAnObjectThatCantBeConvertedToAString() {
		/** @var ViewHelperNode|\PHPUnit_Framework_MockObject_MockObject $mockSyntaxTreeNode */
		$mockSyntaxTreeNode = $this->getMockBuilder(ViewHelperNode::class)->disableOriginalConstructor()->getMock();

		/** @var TemplateCompiler|\PHPUnit_Framework_MockObject_MockObject $mockTemplateCompiler */
		$mockTemplateCompiler = $this->getMockBuilder(TemplateCompiler::class)->disableOriginalConstructor()->getMock();
		$mockTemplateCompiler->expects($this->once())->method('variableName')->with('value')->will($this->returnValue('$value123'));

		$initializationPhpCode = '$arguments = array("value" => new \TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithoutToString("Xaver <b>Cross-Site</b>"), "keepQuotes" => FALSE, "encoding" => "UTF-8", "doubleEncode" => TRUE);' . chr(10);
		$compiledPhpCode = $this->viewHelper->compile('$arguments', 'NULL', $initializationPhpCode, $mockSyntaxTreeNode, $mockTemplateCompiler);
		$result = NULL;
		eval($initializationPhpCode . '$result = ' . $compiledPhpCode . ';');
		$this->assertEquals(new UserWithoutToString("Xaver <b>Cross-Site</b>"), $result);
	}
}
