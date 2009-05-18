<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\SyntaxTree;

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

/**
 * @package
 * @subpackage
 * @version $Id:$
 */
class AbstractNodeTest extends \F3\Testing\BaseTestCase {

	protected $viewHelperContext;

	protected $variableContainer;

	protected $abstractNode;

	protected $childNode;

	public function setUp() {
		$this->viewHelperContext = $this->getMock('F3\Fluid\Core\ViewHelperContext', array(), array(), '', FALSE);
		$this->variableContainer = $this->getMock('F3\Fluid\Core\VariableContainer', array(), array(), '', FALSE);

		$this->abstractNode = $this->getMock('F3\Fluid\Core\SyntaxTree\AbstractNode', array('evaluate'));
		$this->abstractNode->setViewHelperContext($this->viewHelperContext);
		$this->abstractNode->setVariableContainer($this->variableContainer);

		$this->childNode = $this->getMock('F3\Fluid\Core\SyntaxTree\AbstractNode');
		$this->abstractNode->addChildNode($this->childNode);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluateChildNodesPassesViewHelperContextToChildNodes() {
		$this->childNode->expects($this->once())->method('setViewHelperContext')->with($this->viewHelperContext);
		$this->abstractNode->evaluateChildNodes();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluateChildNodesPassesVariableContainerToChildNodes() {
		$this->childNode->expects($this->once())->method('setVariableContainer')->with($this->variableContainer);
		$this->abstractNode->evaluateChildNodes();
	}
}


?>