<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers;

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
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 */

/**
 * Link-generation view helper.
 * 
 * = Examples =
 *
 * <code title="Defaults">
 * <f:actionLink>some link</f:actionLink>
 * </code>
 * 
 * Output:
 * <a href="currentpackage/currentcontroller">some link</a>
 * (depending on routing setup and current package/controller/action)
 *
 * <code title="Additional arguments">
 * <f:actionLink action="myAction" controller="MyController" package="MyPackage" subpackage="MySubpackage" arguments="{key1: 'value1', key2: 'value2'}">some link</f:actionLink>
 * </code>
 * 
 * Output:
 * <a href="mypackage/mycontroller/mysubpackage/myaction?key1=value1&amp;key2=value2">some link</a>
 * (depending on routing setup)
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class ActionLinkViewHelper extends \F3\Fluid\Core\ViewHelper\TagBasedViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'a';

	/**
	 * Initialize arguments
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Render the link.
	 *
	 * @param string $action Name of target action.
	 * @param string $controller Name of target controller. Defaults to current controller.
	 * @param string $package Name of target package
	 * @param string $subpackage Name of target subpackage
	 * @param array $arguments Associative array of all URL arguments which should be appended
	 * @return string The rendered link
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function render($action = NULL, $controller = NULL, $package = NULL, $subpackage = NULL, array $arguments = array()) {
		$uriHelper = $this->variableContainer->get('view')->getViewHelper('F3\FLOW3\MVC\View\Helper\URIHelper');
		$uri = $uriHelper->URIFor($action, $arguments, $controller, $package, $subpackage);
		$this->tag->addAttribute('href', $uri);
		$this->tag->setContent($this->renderChildren());

		return $this->tag->render();
	}
}


?>
