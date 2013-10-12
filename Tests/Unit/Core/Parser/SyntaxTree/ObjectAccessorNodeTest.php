<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for ObjectAccessorNode
 */
class ObjectAccessorNodeTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function evaluateGetsPropertyPathFromVariableContainer() {
		$node = new \TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode('foo.bar');
		$renderingContext = $this->getMock('TYPO3\Fluid\Core\Rendering\RenderingContextInterface');
		$variableContainer = new \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer(array(
			'foo' => array(
				'bar' => 'some value'
			)
		));
		$renderingContext->expects($this->any())->method('getTemplateVariableContainer')->will($this->returnValue($variableContainer));

		$value = $node->evaluate($renderingContext);

		$this->assertEquals('some value', $value);
	}

	/**
	 * @test
	 */
	public function evaluateCallsObjectAccessOnSubjectWithTemplateObjectAccessInterface() {
		$node = new \TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode('foo.bar');
		$renderingContext = $this->getMock('TYPO3\Fluid\Core\Rendering\RenderingContextInterface');
		$templateObjectAcessValue = $this->getMock('TYPO3\Fluid\Core\Parser\SyntaxTree\TemplateObjectAccessInterface');
		$variableContainer = new \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer(array(
			'foo' => array(
				'bar' => $templateObjectAcessValue
			)
		));
		$renderingContext->expects($this->any())->method('getTemplateVariableContainer')->will($this->returnValue($variableContainer));

		$templateObjectAcessValue->expects($this->once())->method('objectAccess')->will($this->returnValue('special value'));

		$value = $node->evaluate($renderingContext);

		$this->assertEquals('special value', $value);
	}

}
