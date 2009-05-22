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
	 * Rendering Context
	 * @var F3\Fluid\Core\Rendering\RenderingContext
	 */
	protected $renderingContext;
	
	/**
	 * Object factory mock
	 * @var F3\FLOW3\Object\FactoryInterface
	 */
	protected $mockObjectFactory;
	
	/**
	 * Template Variable Container
	 * @var F3\Fluid\Core\ViewHelper\TemplateVariableContainer
	 */
	protected $templateVariableContainer;
	
	/**
	 * 
	 * @var F3\FLOW3\MVC\Controller\ControllerContext
	 */
	protected $controllerContext;
	
	/**
	 * Setup fixture
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setUp() {
		$this->renderingContext = new \F3\Fluid\Core\Rendering\RenderingContext();
		
		$this->mockObjectFactory = $this->getMock('F3\FLOW3\Object\FactoryInterface');
		$this->renderingContext->injectObjectFactory($this->mockObjectFactory);
		
		$this->templateVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\TemplateVariableContainer');
		$this->renderingContext->setTemplateVariableContainer($this->templateVariableContainer);
		
		$this->controllerContext = $this->getMock('F3\FLOW3\MVC\Controller\ControllerContext');
		$this->renderingContext->setControllerContext($this->controllerContext);
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function childNodeAccessFacetWorksAsExpected() {
		$childNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\TextNode', array(), array('foo'));

		$mockViewHelper = $this->getMock('F3\Fluid\Core\Parser\Fixtures\ChildNodeAccessFacetViewHelper', array('setChildNodes', 'initializeArguments', 'render', 'prepareArguments', 'setRenderingContext'));

		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);
	
		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('F3\Fluid\ViewHelpers\TestViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode('F3\Fluid\ViewHelpers\TestViewHelper', array());
		$viewHelperNode->addChildNode($childNode);
		
		$mockViewHelper->expects($this->once())->method('setChildNodes')->with($this->equalTo(array($childNode)));
		$mockViewHelper->expects($this->once())->method('setRenderingContext')->with($this->equalTo($this->renderingContext));
		
		$viewHelperNode->setRenderingContext($this->renderingContext);
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

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('F3\Fluid\Core\ViewHelper\AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array());
		
		$viewHelperNode->setRenderingContext($this->renderingContext);
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

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('F3\Fluid\Core\ViewHelper\AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array(
			'param2' => new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('b'),
			'param1' => new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('a'),
		));
		
		$viewHelperNode->setRenderingContext($this->renderingContext);
		$viewHelperNode->evaluate();
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluateMethodPassesControllerContextToViewHelper() {		
		$mockViewHelper = $this->getMock('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setControllerContext'));
		$mockViewHelper->expects($this->once())->method('setControllerContext')->with($this->controllerContext);
		
		$viewHelperNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode('F3\Fluid\Core\ViewHelper\AbstractViewHelper', array());
		$mockViewHelperArguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('F3\Fluid\Core\ViewHelper\AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('F3\Fluid\Core\ViewHelper\Arguments')->will($this->returnValue($mockViewHelperArguments));
		
		$viewHelperNode->setRenderingContext($this->renderingContext);
		$viewHelperNode->evaluate();
	}
}

?>