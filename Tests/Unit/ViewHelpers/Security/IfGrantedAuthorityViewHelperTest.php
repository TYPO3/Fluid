<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Security;

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
 * @version $Id: IfViewHelperTest.php 2813 2009-07-16 14:02:34Z k-fish $
 */
/**
 * Testcase for IfGrantedAuthorityViewHelper
 *
 * @version $Id: IfViewHelperTest.php 2813 2009-07-16 14:02:34Z k-fish $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class IfGrantedAuthorityViewHelperTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function viewHelperRendersChildrenIfHasGrantedAuthorityReturnsTrueAndNoThenViewHelperChildExists() {
		$mockSecurityContext = $this->getMock('F3\FLOW3\Security\Context', array(), array(), '', FALSE);
		$mockSecurityContext->expects($this->once())->method('hasGrantedAuthority')->with('someGA')->will($this->returnValue(TRUE));

		$mockSecurityContextHolder = $this->getMock('F3\FLOW3\Security\ContextHolderInterface', array(), array(), '', FALSE);
		$mockSecurityContextHolder->expects($this->once())->method('getContext')->will($this->returnValue($mockSecurityContext));

		$mockViewHelper = $this->getMock('F3\Fluid\ViewHelpers\Security\IfGrantedAuthorityViewHelper', array('renderChildren', 'hasAccessToResource'));
		$mockViewHelper->injectSecurityContextHolder($mockSecurityContextHolder);
		$mockViewHelper->expects($this->once())->method('renderChildren')->will($this->returnValue('foo'));

		$actualResult = $mockViewHelper->render('someGA');
		$this->assertEquals('foo', $actualResult);
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function viewHelperRendersThenViewHelperChildIfHasGrantedAuthorityReturnsTrueAndThenViewHelperChildExists() {
		$renderingContext = $this->getMock('F3\Fluid\Core\Rendering\RenderingContext');

		$mockThenViewHelperNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockThenViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue('F3\Fluid\ViewHelpers\ThenViewHelper'));
		$mockThenViewHelperNode->expects($this->at(1))->method('setRenderingContext')->with($renderingContext);
		$mockThenViewHelperNode->expects($this->at(2))->method('evaluate')->will($this->returnValue('ThenViewHelperResults'));

		$mockSecurityContext = $this->getMock('F3\FLOW3\Security\Context', array(), array(), '', FALSE);
		$mockSecurityContext->expects($this->once())->method('hasGrantedAuthority')->with('someGA')->will($this->returnValue(TRUE));

		$mockSecurityContextHolder = $this->getMock('F3\FLOW3\Security\ContextHolderInterface', array(), array(), '', FALSE);
		$mockSecurityContextHolder->expects($this->once())->method('getContext')->will($this->returnValue($mockSecurityContext));

		$viewHelper = $this->getMock('F3\Fluid\ViewHelpers\Security\IfGrantedAuthorityViewHelper', array('dummy'));
		$viewHelper->injectSecurityContextHolder($mockSecurityContextHolder);

		$viewHelper->setChildNodes(array($mockThenViewHelperNode));
		$viewHelper->setRenderingContext($renderingContext);
		$actualResult = $viewHelper->render('someGA');

		$this->assertEquals('ThenViewHelperResults', $actualResult);
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function renderReturnsEmptyStringIfHasGrantedAuthorityReturnsFalseAndNoElseViewHelperChildExists() {
		$mockSecurityContext = $this->getMock('F3\FLOW3\Security\Context', array(), array(), '', FALSE);
		$mockSecurityContext->expects($this->once())->method('hasGrantedAuthority')->with('someGA')->will($this->returnValue(FALSE));

		$mockSecurityContextHolder = $this->getMock('F3\FLOW3\Security\ContextHolderInterface', array(), array(), '', FALSE);
		$mockSecurityContextHolder->expects($this->once())->method('getContext')->will($this->returnValue($mockSecurityContext));

		$viewHelper = $this->getMock('F3\Fluid\ViewHelpers\Security\IfGrantedAuthorityViewHelper', array('dummy'));
		$viewHelper->injectSecurityContextHolder($mockSecurityContextHolder);

		$actualResult = $viewHelper->render('someGA');
		$this->assertEquals('', $actualResult);
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function viewHelperRendersElseViewHelperChildIfHasGrantedAuthorityReturnsFalseAndElseViewHelperChildExists() {
		$renderingContext = $this->getMock('F3\Fluid\Core\Rendering\RenderingContext');

		$mockElseViewHelperNode = $this->getMock('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', array('getViewHelperClassName', 'evaluate', 'setRenderingContext'), array(), '', FALSE);
		$mockElseViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue('F3\Fluid\ViewHelpers\ElseViewHelper'));
		$mockElseViewHelperNode->expects($this->at(1))->method('setRenderingContext')->with($renderingContext);
		$mockElseViewHelperNode->expects($this->at(2))->method('evaluate')->will($this->returnValue('ElseViewHelperResults'));

		$mockSecurityContext = $this->getMock('F3\FLOW3\Security\Context', array(), array(), '', FALSE);
		$mockSecurityContext->expects($this->once())->method('hasGrantedAuthority')->with('someGA')->will($this->returnValue(FALSE));

		$mockSecurityContextHolder = $this->getMock('F3\FLOW3\Security\ContextHolderInterface', array(), array(), '', FALSE);
		$mockSecurityContextHolder->expects($this->once())->method('getContext')->will($this->returnValue($mockSecurityContext));

		$viewHelper = $this->getMock('F3\Fluid\ViewHelpers\Security\IfGrantedAuthorityViewHelper', array('dummy'));
		$viewHelper->injectSecurityContextHolder($mockSecurityContextHolder);

		$viewHelper->setChildNodes(array($mockElseViewHelperNode));
		$viewHelper->setRenderingContext($this->getMock('F3\Fluid\Core\Rendering\RenderingContext'));

		$actualResult = $viewHelper->render('someGA');
		$this->assertEquals('ElseViewHelperResults', $actualResult);
	}
}

?>
