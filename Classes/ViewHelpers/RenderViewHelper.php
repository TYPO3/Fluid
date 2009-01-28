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
 * [Enter description here]
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class RenderViewHelper extends \F3\Fluid\Core\AbstractViewHelper {
	public function initializeArguments() {
		$this->registerArgument('section', 'string', 'Name of section to render. If used in a layout, renders a section of the main content file. If used inside a standard template, renders a section of the same file.', TRUE);
	}
	
	public function render() {
		return $this->variableContainer->get('view')->renderSection($this->arguments['section']);
	}
}


?>
