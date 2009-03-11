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
 * [Enter description here]
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class RenderViewHelper extends \F3\Fluid\Core\AbstractViewHelper {

	/**
	 * Initializes the arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
		$this->registerArgument('section', 'string', 'Name of section to render. If used in a layout, renders a section of the main content file. If used inside a standard template, renders a section of the same file.', FALSE);
		$this->registerArgument('partial', 'string', 'Reference to a partial.', FALSE);
		$this->registerArgument('arguments', 'array', 'Arguments to pass to the partial', FALSE);
	}

	/**
	 * Renders the content.
	 *
	 * @return string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render() {
		if ($this->arguments['partial']) {
			$arguments = $this->arguments['arguments'];
			if (!is_array($arguments)) {
				$arguments = array();
			}
			return $this->variableContainer->get('view')->renderPartial($this->arguments['partial'], $this->arguments['section'], $arguments);
		} elseif ($this->arguments['section']) {
			return $this->variableContainer->get('view')->renderSection($this->arguments['section']);
		}
		return '';
	}


}


?>
