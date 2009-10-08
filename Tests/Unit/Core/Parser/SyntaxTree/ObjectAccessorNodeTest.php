<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Parser\SyntaxTree;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

require_once(__DIR__ . '/../Fixtures/SomeEmptyClass.php');

/**
 * Testcase for ObjectAccessor
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class ObjectAccessorNodeTest extends \F3\Testing\BaseTestCase {

	protected $mockTemplateVariableContainer;

	protected $renderingContext;

	protected $renderingConfiguration;

	public function setUp() {
		$this->mockTemplateVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\TemplateVariableContainer');
		$this->renderingContext = new \F3\Fluid\Core\Rendering\RenderingContext();
		$this->renderingContext->setTemplateVariableContainer($this->mockTemplateVariableContainer);
		$this->renderingConfiguration = $this->getMock('F3\Fluid\Core\Rendering\RenderingConfiguration');
		$this->renderingContext->setRenderingConfiguration($this->renderingConfiguration);
	}
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function objectAccessorWorksWithStrings() {
		$objectAccessorNode = new \F3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode('exampleObject');
		$objectAccessorNode->setRenderingContext($this->renderingContext);

		$this->mockTemplateVariableContainer->expects($this->at(0))->method('get')->with('exampleObject')->will($this->returnValue('ExpectedString'));

		$actualResult = $objectAccessorNode->evaluate();
		$this->assertEquals('ExpectedString', $actualResult, 'ObjectAccessorNode did not work for string input.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function objectAccessorWorksWithNestedObjects() {
		$exampleObject = new \F3\Fluid\Core\Parser\Fixtures\SomeEmptyClass('Foo');

		$objectAccessorNode = new \F3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode('exampleObject.subproperty');
		$objectAccessorNode->setRenderingContext($this->renderingContext);

		$this->mockTemplateVariableContainer->expects($this->at(0))->method('get')->with('exampleObject')->will($this->returnValue($exampleObject));

		$actualResult = $objectAccessorNode->evaluate();
		$this->assertEquals('Foo', $actualResult, 'ObjectAccessorNode did not work for calling getters.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function objectAccessorWorksWithDirectProperties() {
		$expectedResult = 'This is a test';
		$exampleObject = new \F3\Fluid\Core\Parser\Fixtures\SomeEmptyClass('');
		$exampleObject->publicVariable = $expectedResult;

		$objectAccessorNode = new \F3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode('exampleObject.publicVariable');
		$objectAccessorNode->setRenderingContext($this->renderingContext);

		$this->mockTemplateVariableContainer->expects($this->at(0))->method('get')->with('exampleObject')->will($this->returnValue($exampleObject));

		$actualResult = $objectAccessorNode->evaluate();
		$this->assertEquals($expectedResult, $actualResult, 'ObjectAccessorNode did not work for direct properties.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function objectAccessorWorksOnAssociativeArrays() {
		$expectedResult = 'My value';
		$exampleArray = array('key' => array('key2' => $expectedResult));

		$objectAccessorNode = new \F3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode('variable.key.key2');
		$objectAccessorNode->setRenderingContext($this->renderingContext);

		$this->mockTemplateVariableContainer->expects($this->at(0))->method('get')->with('variable')->will($this->returnValue($exampleArray));

		$actualResult = $objectAccessorNode->evaluate();
		$this->assertEquals($expectedResult, $actualResult, 'ObjectAccessorNode did not traverse associative arrays.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @expectedException \F3\Fluid\Core\RuntimeException
	 */
	public function objectAccessorThrowsExceptionIfKeyInAssociativeArrayDoesNotExist() {
		$this->markTestIncomplete('Objects accessors fail silently so far. We need some context dependencies here.');
		$expected = 'My value';
		$exampleArray = array('key' => array('key2' => $expected));
		$objectAccessorNode = new \F3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode('variable.key.key3');
		$context = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array('variable' => $exampleArray));

		$actual = $objectAccessorNode->evaluate();
	}

	/**
	 * @test
	 * @expectedException \F3\Fluid\Core\RuntimeException
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectAccessorThrowsErrorIfPropertyDoesNotExist() {
		$this->markTestIncomplete('Objects accessors fail silently so far. We need some context dependencies here.');

		$expected = 'This is a test';
		$exampleObject = new \F3\Fluid\SomeEmptyClass("Hallo");
		$exampleObject->publicVariable = $expected;
		$objectAccessorNode = new \F3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode("exampleObject.publicVariableNotExisting");
		$context = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array('exampleObject' => $exampleObject));

		$actual = $objectAccessorNode->evaluate($context);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectAccessorPostProcessorIsCalled() {
		$objectAccessorNode = new \F3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode('variable');
		$objectAccessorNode->setRenderingContext($this->renderingContext);

		$this->mockTemplateVariableContainer->expects($this->at(0))->method('get')->with('variable')->will($this->returnValue('hallo'));

		$this->renderingContext->setObjectAccessorPostProcessorEnabled(TRUE);

		$objectAccessorPostProcessor = $this->getMock('F3\Fluid\Core\Rendering\ObjectAccessorPostProcessorInterface');
		$this->renderingConfiguration->expects($this->once())->method('getObjectAccessorPostProcessor')->will($this->returnValue($objectAccessorPostProcessor));
		$objectAccessorPostProcessor->expects($this->once())->method('process')->with('hallo', TRUE)->will($this->returnValue('PostProcessed'));
		$this->assertEquals('PostProcessed', $objectAccessorNode->evaluate());
	}

}

?>