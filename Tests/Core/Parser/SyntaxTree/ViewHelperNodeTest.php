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

/**
 * @package
 * @subpackage
 * @version $Id$
 */
/**
 * Testcase for [insert classname here]
 *
 * @package
 * @subpackage Tests
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
include_once(__DIR__ . '/../Fixtures/ChildNodeAccessFacetViewHelper.php');
class ViewHelperNodeTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function childNodeAccessFacetWorksAsExpected() {
		$childNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\TextNode', array(), array('foo'));

		$mockViewHelper = $this->getMock('F3\Fluid\ChildNodeAccessFacetViewHelper', array('setChildNodes', 'initializeArguments', 'render', 'prepareArguments'));
		$mockViewHelper->expects($this->once())->method('setChildNodes')->with($this->equalTo(array($childNode)));

		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$mockObjectFactory = $this->getMock('F3\FLOW3\Object\FactoryInterface');
		$mockObjectFactory->expects($this->at(0))->method('create')->with('F3\Fluid\ViewHelpers\TestViewHelper')->will($this->returnValue($mockViewHelper));
		$mockObjectFactory->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$mockVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\VariableContainer');
		$mockVariableContainer->expects($this->at(0))->method('getObjectFactory')->will($this->returnValue($mockObjectFactory));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode('F3\Fluid\ViewHelpers\TestViewHelper', array());
		$viewHelperNode->addChildNode($childNode);
		$viewHelperNode->setVariableContainer($mockVariableContainer);
		$viewHelperNode->setViewHelperContext($this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperContext'));
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function validateArgumentsIsCalledByViewHelperNode() {
		$mockViewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments'));
		$mockViewHelper->expects($this->once())->method('validateArguments');

		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$mockObjectFactory = $this->getMock('F3\FLOW3\Object\FactoryInterface');
		$mockObjectFactory->expects($this->at(0))->method('create')->with('F3\Fluid\Core\ViewHelper\AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$mockObjectFactory->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$mockVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\VariableContainer');
		$mockVariableContainer->expects($this->at(0))->method('getObjectFactory')->will($this->returnValue($mockObjectFactory));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array());
		$viewHelperNode->setVariableContainer($mockVariableContainer);
		$viewHelperNode->setViewHelperContext($this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperContext'));
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderMethodIsCalledWithCorrectArguments() {
		$arguments = array(
			'param0' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param1', 'string', 'Hallo', TRUE, null, FALSE),
			'param1' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param1', 'string', 'Hallo', TRUE, null, TRUE),
			'param2' => new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('param2', 'string', 'Hallo', TRUE, null, TRUE)
		);

		$mockViewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments'));
		$mockViewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue($arguments));
		$mockViewHelper->expects($this->once())->method('render')->with('a', 'b');

		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$mockObjectFactory = $this->getMock('F3\FLOW3\Object\FactoryInterface');
		$mockObjectFactory->expects($this->at(0))->method('create')->with('F3\Fluid\Core\ViewHelper\AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$mockObjectFactory->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$mockVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\VariableContainer');
		$mockVariableContainer->expects($this->at(0))->method('getObjectFactory')->will($this->returnValue($mockObjectFactory));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array(
			'param2' => new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('b'),
			'param1' => new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('a'),
		));
		$viewHelperNode->setVariableContainer($mockVariableContainer);
		$viewHelperNode->setViewHelperContext($this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperContext'));
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function evaluateMethodPassesViewHelperContextToViewHelper() {
		$mockViewHelperContext = $this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperContext', array(), array(), '', FALSE);

		$mockViewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setViewHelperContext'));
		#$mockViewHelper->expects($this->at(0))->method('setViewHelperContext')->with($mockViewHelperContext);

		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$mockObjectFactory = $this->getMock('F3\FLOW3\Object\FactoryInterface');
		$mockObjectFactory->expects($this->at(0))->method('create')->with('F3\Fluid\Core\ViewHelper\AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$mockObjectFactory->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$mockVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\VariableContainer');
		$mockVariableContainer->expects($this->at(0))->method('getObjectFactory')->will($this->returnValue($mockObjectFactory));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array());
		$viewHelperNode->setVariableContainer($mockVariableContainer);
		$viewHelperNode->setViewHelperContext($this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperContext'));
		$viewHelperNode->evaluate();
	}
}



?>
