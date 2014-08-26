<?php
namespace TYPO3\Fluid\Tests\Unit\ViewHelpers\Format;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/../Fixtures/UserWithoutToString.php');
require_once(__DIR__ . '/../Fixtures/UserWithToString.php');
require_once(__DIR__ . '/../ViewHelperBaseTestcase.php');

use TYPO3\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3\Fluid\ViewHelpers\Fixtures\UserWithoutToString;
use TYPO3\Fluid\ViewHelpers\Fixtures\UserWithToString;
use TYPO3\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper;
use TYPO3\Fluid\ViewHelpers\ViewHelperBaseTestcase;

/**
 * Test for \TYPO3\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper
 */
class HtmlspecialcharsViewHelperTest extends ViewHelperBaseTestcase {

	/**
	 * @var HtmlspecialcharsViewHelper|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getMock('TYPO3\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper', array('renderChildren'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 */
	public function viewHelperDeactivatesEscapingInterceptor() {
		$this->assertFalse($this->viewHelper->isEscapingInterceptorEnabled());
	}

	/**
	 * @test
	 */
	public function renderUsesValueAsSourceIfSpecified() {
		$this->viewHelper->expects($this->never())->method('renderChildren');
		$actualResult = $this->viewHelper->render('Some string');
		$this->assertEquals('Some string', $actualResult);
	}

	/**
	 * __test
	 */
	public function renderUsesChildNodesAsSourceIfSpecified() {
		$this->viewHelper->expects($this->atLeastOnce())->method('renderChildren')->will($this->returnValue('Some string'));
		$actualResult = $this->viewHelper->render();
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
				'expectedResult' => 'Some special characters: &amp;©&quot;\''
			),
			// render respects "keepQuotes" argument
			array(
				'value' => 'Some special characters: &©"\'',
				'options' => array(
					'keepQuotes' => TRUE,
				),
				'expectedResult' => 'Some special characters: &amp;©"\''
			),
			// render respects "encoding" argument
			array(
				'value' => utf8_decode('Some special characters: &"\''),
				'options' => array(
					'encoding' => 'ISO-8859-1',
				),
				'expectedResult' => 'Some special characters: &amp;&quot;\''
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
	 * __test
	 *
	 * @dataProvider dataProvider
	 */
	public function renderTests($value, array $options, $expectedResult) {
		$this->assertSame($expectedResult, $this->viewHelper->render($value, isset($options['keepQuotes']) ? $options['keepQuotes'] : FALSE, isset($options['encoding']) ? $options['encoding'] : 'UTF-8', isset($options['doubleEncode']) ? $options['doubleEncode'] : TRUE));
	}

	/**
	 * @test
	 * @dataProvider dataProvider
	 */
	public function renderTestsWithRenderChildrenFallback($value, array $options, $expectedResult) {
		$this->viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue($value));
		$this->assertSame($expectedResult, $this->viewHelper->render(NULL, isset($options['keepQuotes']) ? $options['keepQuotes'] : FALSE, isset($options['encoding']) ? $options['encoding'] : 'UTF-8', isset($options['doubleEncode']) ? $options['doubleEncode'] : TRUE));
	}

	/**
	 * __test
	 *
	 * @dataProvider dataProvider
	 */
	public function compileTests($value, array $options, $expectedResult) {
		/** @var AbstractNode|\PHPUnit_Framework_MockObject_MockObject $mockSyntaxTreeNode */
		$mockSyntaxTreeNode = $this->getMockBuilder('TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode')->disableOriginalConstructor()->getMock();

		/** @var TemplateCompiler|\PHPUnit_Framework_MockObject_MockObject $mockTemplateCompiler */
		$mockTemplateCompiler = $this->getMockBuilder('TYPO3\Fluid\Core\Compiler\TemplateCompiler')->disableOriginalConstructor()->getMock();
		$mockTemplateCompiler->expects($this->once())->method('variableName')->with('value')->will($this->returnValue('$value123'));

		$arguments = array(
			'value' => $value,
			'keepQuotes' => FALSE,
			'encoding' => 'UTF-8',
			'doubleEncode' => TRUE,
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
		/** @var AbstractNode|\PHPUnit_Framework_MockObject_MockObject $mockSyntaxTreeNode */
		$mockSyntaxTreeNode = $this->getMockBuilder('TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode')->disableOriginalConstructor()->getMock();

		/** @var TemplateCompiler|\PHPUnit_Framework_MockObject_MockObject $mockTemplateCompiler */
		$mockTemplateCompiler = $this->getMockBuilder('TYPO3\Fluid\Core\Compiler\TemplateCompiler')->disableOriginalConstructor()->getMock();
		$mockTemplateCompiler->expects($this->once())->method('variableName')->with('value')->will($this->returnValue('$value123'));

		$renderChildrenClosureName = uniqid('renderChildren');
		$arguments = array(
			'value' => NULL,
			'keepQuotes' => FALSE,
			'encoding' => 'UTF-8',
			'doubleEncode' => TRUE,
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
		$actualResult = $this->viewHelper->render($user);
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * __test
	 */
	public function renderDoesNotModifySourceIfItIsAnObjectThatCantBeConvertedToAString() {
		$user = new UserWithoutToString('Xaver <b>Cross-Site</b>');
		$actualResult = $this->viewHelper->render($user);
		$this->assertSame($user, $actualResult);
	}

	/**
	 * __test
	 */
	public function compileConvertsObjectsToStrings() {
		/** @var AbstractNode|\PHPUnit_Framework_MockObject_MockObject $mockSyntaxTreeNode */
		$mockSyntaxTreeNode = $this->getMockBuilder('TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode')->disableOriginalConstructor()->getMock();

		/** @var TemplateCompiler|\PHPUnit_Framework_MockObject_MockObject $mockTemplateCompiler */
		$mockTemplateCompiler = $this->getMockBuilder('TYPO3\Fluid\Core\Compiler\TemplateCompiler')->disableOriginalConstructor()->getMock();
		$mockTemplateCompiler->expects($this->once())->method('variableName')->with('value')->will($this->returnValue('$value123'));

		$initializationPhpCode = '$arguments = array("value" => new \TYPO3\Fluid\ViewHelpers\Fixtures\UserWithToString("Xaver <b>Cross-Site</b>"), "keepQuotes" => FALSE, "encoding" => "UTF-8", "doubleEncode" => TRUE);' . chr(10);
		$compiledPhpCode = $this->viewHelper->compile('$arguments', 'NULL', $initializationPhpCode, $mockSyntaxTreeNode, $mockTemplateCompiler);
		$result = NULL;
		eval($initializationPhpCode . '$result = ' . $compiledPhpCode . ';');
		$this->assertSame('Xaver &lt;b&gt;Cross-Site&lt;/b&gt;', $result);
	}

	/**
	 * @test
	 */
	public function compileDoesNotModifySourceIfItIsAnObjectThatCantBeConvertedToAString() {
		/** @var AbstractNode|\PHPUnit_Framework_MockObject_MockObject $mockSyntaxTreeNode */
		$mockSyntaxTreeNode = $this->getMockBuilder('TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode')->disableOriginalConstructor()->getMock();

		/** @var TemplateCompiler|\PHPUnit_Framework_MockObject_MockObject $mockTemplateCompiler */
		$mockTemplateCompiler = $this->getMockBuilder('TYPO3\Fluid\Core\Compiler\TemplateCompiler')->disableOriginalConstructor()->getMock();
		$mockTemplateCompiler->expects($this->once())->method('variableName')->with('value')->will($this->returnValue('$value123'));

		$initializationPhpCode = '$arguments = array("value" => new \TYPO3\Fluid\ViewHelpers\Fixtures\UserWithoutToString("Xaver <b>Cross-Site</b>"), "keepQuotes" => FALSE, "encoding" => "UTF-8", "doubleEncode" => TRUE);' . chr(10);
		$compiledPhpCode = $this->viewHelper->compile('$arguments', 'NULL', $initializationPhpCode, $mockSyntaxTreeNode, $mockTemplateCompiler);
		$result = NULL;
		eval($initializationPhpCode . '$result = ' . $compiledPhpCode . ';');
		$this->assertEquals(new UserWithoutToString("Xaver <b>Cross-Site</b>"), $result);
	}
}
