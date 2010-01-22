<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Parser\SyntaxTree;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for [insert classname here]
 *
 * @version $Id: ViewHelperNodeTest.php 2411 2009-05-26 22:00:04Z sebastian $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ViewHelperNodeComparatorTest extends \F3\Testing\BaseTestCase {

	/**
	 * Rendering Context
	 * @var F3\Fluid\Core\Rendering\RenderingContext
	 */
	protected $renderingContext;

	/**
	 * Object factory mock
	 * @var F3\FLOW3\Object\ObjectFactoryInterface
	 */
	protected $mockObjectFactory;

	/**
	 * Template Variable Container
	 * @var F3\Fluid\Core\ViewHelper\TemplateVariableContainer
	 */
	protected $templateVariableContainer;

	/**
	 *
	 * @var F3\FLOW3\MVC\Controller\Context
	 */
	protected $controllerContext;

	/**
	 * @var F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * @var F3\Fluid\Core\Parser\TemplateParser
	 */
	protected $templateParser;

	/**
	 * @var F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
	 */
	protected $viewHelperNode;

	/**
	 * Setup fixture
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setUp() {
		$this->renderingContext = new \F3\Fluid\Core\Rendering\RenderingContext();

		$this->mockObjectFactory = $this->getMock('F3\FLOW3\Object\ObjectFactoryInterface');
		$this->renderingContext->injectObjectFactory($this->mockObjectFactory);

		$this->templateVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\TemplateVariableContainer', array('dummy'));
		$this->renderingContext->setTemplateVariableContainer($this->templateVariableContainer);

		$this->controllerContext = $this->getMock('F3\FLOW3\MVC\Controller\Context', array(), array(), '', FALSE);
		$this->renderingContext->setControllerContext($this->controllerContext);

		$this->viewHelperVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$this->renderingContext->setViewHelperVariableContainer($this->viewHelperVariableContainer);

		$this->templateParser = $this->objectManager->getObject('F3\Fluid\Core\Parser\TemplateParser');

		$this->viewHelperNode = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode'), array('dummy'), array(), '', FALSE);
		$this->viewHelperNode->setRenderingContext($this->renderingContext);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function comparingEqualNumbersReturnsTrue() {
		$expression = '5==5';
		$expected = TRUE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function comparingEqualNumbersWithSpacesReturnsTrue() {
		$expression = '   5 ==5';
		$expected = TRUE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function comparingUnequalNumbersReturnsFals() {
		$expression = '5==3';
		$expected = FALSE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function comparingEqualObjectsWithSpacesReturnsTrue() {
		$expression = '{value1} =={value2}';
		$expected = TRUE;
		$this->templateVariableContainer->add('value1', 'Hello everybody');
		$this->templateVariableContainer->add('value2', 'Hello everybody');

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function comparingUnequalObjectsWithSpacesReturnsFalse() {
		$expression = '{value1} =={value2}';
		$expected = FALSE;
		$this->templateVariableContainer->add('value1', 'Hello everybody');
		$this->templateVariableContainer->add('value2', 'Hello nobody');

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function comparingEqualNumberStoredInVariableWithNumberReturnsTrue() {
		$expression = '{value1} ==42';
		$expected = TRUE;
		$this->templateVariableContainer->add('value1', '42');

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function comparingUnequalNumberStoredInVariableWithNumberReturnsFalse() {
		$expression = '{value1} ==42';
		$expected = FALSE;
		$this->templateVariableContainer->add('value1', '41');

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function notEqualReturnsFalseIfNumbersAreEqual() {
		$expression = '5!=5';
		$expected = FALSE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function notEqualReturnsTrueIfNumbersAreNotEqual() {
		$expression = '5!=3';
		$expected = TRUE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function notEqualReturnsFalseForTwoObjectsWithEqualValues() {
		$expression = '{value1} !={value2}';
		$expected = FALSE;
		$this->templateVariableContainer->add('value1', 'Hello everybody');
		$this->templateVariableContainer->add('value2', 'Hello everybody');

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function notEqualReturnsTrueForTwoObjectsWithUnequalValues() {
		$expression = '{value1} !={value2}';
		$expected = TRUE;
		$this->templateVariableContainer->add('value1', 'Hello everybody');
		$this->templateVariableContainer->add('value2', 'Hello nobody');

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function notEqualReturnsFalseForOneObjectAndOneNumberWithEqualValues() {
		$expression = '{value1} !=42';
		$expected = FALSE;
		$this->templateVariableContainer->add('value1', '42');

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function notEqualReturnsTrueForOneObjectAndOneNumberWithUnequalValues() {
		$expression = '{value1} !=42';
		$expected = TRUE;
		$this->templateVariableContainer->add('value1', '41');

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function oddNumberModulo2ReturnsTrue() {
		$expression = '43 % 2';
		$expected = TRUE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evenNumberModulo2ReturnsFalse() {
		$expression = '42 % 2';
		$expected = FALSE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterThanReturnsTrueIfNumberIsReallyGreater() {
		$expression = '10 > 9';
		$expected = TRUE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterThanReturnsFalseIfNumberIsEqual() {
		$expression = '10 > 10';
		$expected = FALSE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterOrEqualsReturnsTrueIfNumberIsReallyGreater() {
		$expression = '10 >= 9';
		$expected = TRUE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterOrEqualsReturnsTrueIfNumberIsEqual() {
		$expression = '10 >= 10';
		$expected = TRUE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function greaterOrEqualsReturnFalseIfNumberIsSmaller() {
		$expression = '10 >= 11';
		$expected = FALSE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessThanReturnsTrueIfNumberIsReallyless() {
		$expression = '9 < 10';
		$expected = TRUE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessThanReturnsFalseIfNumberIsEqual() {
		$expression = '10 < 10';
		$expected = FALSE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessOrEqualsReturnsTrueIfNumberIsReallyLess() {
		$expression = '9 <= 10';
		$expected = TRUE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessOrEqualsReturnsTrueIfNumberIsEqual() {
		$expression = '10 <= 10';
		$expected = TRUE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function lessOrEqualsReturnFalseIfNumberIsBigger() {
		$expression = '11 <= 10';
		$expected = FALSE;

		$parsedTemplate = $this->templateParser->parse($expression);
		$result = $this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 * @expectedException \RuntimeException
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function havingMoreThanThreeElementsInTheSyntaxTreeThrowsException() {
		$expression = '   5 ==5 {blubb} {bla} {blu}';

		$parsedTemplate = $this->templateParser->parse($expression);
		$this->viewHelperNode->_call('evaluateBooleanExpression', $parsedTemplate->getRootNode());
	}

}

?>