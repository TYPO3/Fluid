<?php
declare(ENCODING = 'utf-8');
namespace F3::Beer3::ViewHelpers;

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
 * @package Beer3
 * @subpackage ViewHelpers
 * @version $Id:$
 */
/**
 * Form view helper. Generates a <form> Tag.
 *
 * @package Beer3
 * @subpackage ViewHelpers
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class FormViewHelper extends F3::Beer3::Core::AbstractViewHelper {
	
	public function injectUriHelper(F3::FLOW3::MVC::View::Helper::URIHelper $uriHelper) {
		$this->uriHelper = $uriHelper;
	}
	
	public function initializeArguments() {
		$this->registerArgument('controller', 'string', 'name of controller to call the current action on');
		$this->registerArgument('action', 'string', 'name of action to call');
		$this->registerArgument('package', 'string', 'name of package to call');
	}

	/**
	 * Render the form.
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render() {
		$currentRequest = $this->variableContainer->get('view')->getRequest();
		$this->uriHelper->setRequest($currentRequest);
		
		$action = ( $this->arguments['action'] ? $this->arguments['action'] : $currentRequest->getControllerActionName() );
		$method = ( $this->arguments['method'] ? $this->arguments['method'] : 'GET' );
		
		$formActionUrl = $this->uriHelper->URIFor($action, array(), $this->arguments['controller'], $this->arguments['package']);
		
		$out = '<form action="' . $formActionUrl . '" enctype="multipart/form-data" method="' . $method . '">';
		$out .= $this->renderChildren();
		$out .= '</form>';
		
		return $out;
	}
}

?>