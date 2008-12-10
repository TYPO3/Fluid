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
/**
 * Testcase for [insert classname here]
 *
 * @package
 * @subpackage Tests
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
include_once(__DIR__ . '/../Fixtures/F3_Fluid_ChildNodeAccessFacetViewHelper.php');
class ViewHelperNodeTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function childNodeAccessFacetWorksAsExpected() {
		$childNode = new \F3\Fluid\Core\SyntaxTree\TextNode("Hallo");
		
		$stubViewHelper = $this->getMock('F3\Fluid\ChildNodeAccessFacetViewHelper', array('setChildNodes', 'initializeArguments', 'render'));
		$stubViewHelper->expects($this->once())
		               ->method('setChildNodes')
		               ->with($this->equalTo(array($childNode)));
		               
		$viewHelperNode = new \F3\Fluid\Core\SyntaxTree\ViewHelperNode("\F3\Fluid\ViewHelpers\TestViewHelper", $stubViewHelper, array());
		$viewHelperNode->addChildNode($childNode);

		$viewHelperNode->render(new \F3\Fluid\Core\VariableContainer(array($childNode)));
	}
	
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArgumentsIsCalledByViewHelperNode() {
		$stubViewHelper = $this->getMock('F3\Fluid\Core\AbstractViewHelper');
		$stubViewHelper->expects($this->once())
		               ->method('initializeArguments');
		$viewHelperNode = new \F3\Fluid\Core\SyntaxTree\ViewHelperNode("\F3\Fluid\Core\AbstractViewHelper", $stubViewHelper, array());
		
		$viewHelperNode->render(new \F3\Fluid\Core\VariableContainer(array()));
	}
}



?>
