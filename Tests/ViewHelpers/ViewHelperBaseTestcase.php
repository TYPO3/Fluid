<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @version $Id$
 */
abstract class ViewHelperBaseTestcase extends \F3\Testing\BaseTestCase {

	/**
	 * @var \F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * @var \F3\Fluid\Core\ViewHelper\TemplateVariableContainer
	 */
	protected $templateVariableContainer;

	/**
	 * @var \F3\FLOW3\MVC\Web\Routing\URIBuilder
	 */
	protected $uriBuilder;

	/**
	 * @var \F3\FLOW3\MVC\Controller\ControllerContext
	 */
	protected $controllerContext;

	/**
	 * @var \F3\Fluid\Core\ViewHelper\TagBuilder
	 */
	protected $tagBuilder;

	/**
	 * @var \F3\Fluid\Core\ViewHelper\Arguments
	 */
	protected $arguments;

	/**
	 * @var \F3\FLOW3\MVC\Web\Request
	 */
	protected $request;

	/**
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setUp() {
		$this->viewHelperVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$this->templateVariableContainer = $this->getMock('F3\Fluid\Core\ViewHelper\TemplateVariableContainer');
		$this->uriBuilder = $this->getMock('F3\FLOW3\MVC\Web\Routing\URIBuilder');
		$this->uriBuilder->expects($this->any())->method('reset')->will($this->returnValue($this->uriBuilder));
		$this->uriBuilder->expects($this->any())->method('setArguments')->will($this->returnValue($this->uriBuilder));
		$this->uriBuilder->expects($this->any())->method('setSection')->will($this->returnValue($this->uriBuilder));
		$this->uriBuilder->expects($this->any())->method('setFormat')->will($this->returnValue($this->uriBuilder));
		$this->uriBuilder->expects($this->any())->method('setCreateAbsoluteUri')->will($this->returnValue($this->uriBuilder));
		$this->uriBuilder->expects($this->any())->method('setAddQueryString')->will($this->returnValue($this->uriBuilder));
		$this->uriBuilder->expects($this->any())->method('setArgumentsToBeExcludedFromQueryString')->will($this->returnValue($this->uriBuilder));
		$this->request = $this->getMock('F3\FLOW3\MVC\Web\Request');
		$this->controllerContext = $this->getMock('F3\FLOW3\MVC\Controller\ControllerContext');
		$this->controllerContext->expects($this->any())->method('getURIBuilder')->will($this->returnValue($this->uriBuilder));
		$this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
		$this->tagBuilder = $this->getMock('F3\Fluid\Core\ViewHelper\TagBuilder');
		$this->arguments = $this->getMock('F3\Fluid\Core\ViewHelper\Arguments', array(), array(), '', FALSE);
	}

	/**
	 * @param \F3\Fluid\Core\ViewHelper\AbstractViewHelper $viewHelper
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function injectDependenciesIntoViewHelper(\F3\Fluid\Core\ViewHelper\AbstractViewHelper $viewHelper) {
		$viewHelper->setViewHelperVariableContainer($this->viewHelperVariableContainer);
		$viewHelper->setTemplateVariableContainer($this->templateVariableContainer);
		$viewHelper->setControllerContext($this->controllerContext);
		$viewHelper->setArguments($this->arguments);
		if ($viewHelper instanceof \F3\Fluid\Core\ViewHelper\TagBasedViewHelper) {
			$viewHelper->injectTagBuilder($this->tagBuilder);
		}
	}
}
?>