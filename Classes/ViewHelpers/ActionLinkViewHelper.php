<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers;

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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
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
