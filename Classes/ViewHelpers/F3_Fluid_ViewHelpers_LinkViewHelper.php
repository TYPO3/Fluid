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
 * @version $Id:$
 */
/**
 * Link-generation view helper
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class LinkViewHelper extends \F3\Fluid\Core\TagBasedViewHelper {
	
	/**
	 * Initialize arguments
	 * 
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @todo Implement support for controller and package arguments
	 * @todo let it inherit from TagBasedViewHelper
	 */
	public function initializeArguments() {
		$this->registerArgument('action', 'string', 'Name of action where the link points to', TRUE);
		$this->registerArgument('controller', 'string', 'Name of controller where the link points to');
		$this->registerArgument('arguments', 'array', 'Associative array of all URL arguments which should be appended.');
		
		$this->registerUniversalTagAttributes();
	}
	
	/**
	 * Render the link.
	 * 
	 * @return string The rendered link
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render() {
		$uriHelper = $this->variableContainer->get('view')->getViewHelper('\F3\FLOW3\MVC\View\Helper\URIHelper');
		$out = '<a href="' . $uriHelper->URIFor($this->arguments['action'], $this->arguments['arguments'], $this->arguments['controller']) . '" ' . $this->renderTagAttributes() . '>';
		$out .= $this->renderChildren();
		$out .= '</a>';
		return $out;
	}
}


?>
