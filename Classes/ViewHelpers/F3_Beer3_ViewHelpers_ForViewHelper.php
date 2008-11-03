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
 * Default view helper
 *
 * @package Beer3
 * @subpackage ViewHelpers
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class ForViewHelper extends F3::Beer3::AbstractViewHelper {
	public function initializeArguments() {
		
	}
	/**
	 * This is some test
	 *
	 * @argument as string Name of the object.
	 * @argument each object Thing to iterate over
	 * @param NodeInterface $node
	 * @param unknown_type $arguments
	 * @return unknown
	 */
	public function render() {
		$out = '';
		foreach ($this->arguments['each'] as $singleElement) {
			$this->variableContainer->add($this->arguments['as'], $singleElement);
			$out .= $this->renderChildren();
			$this->variableContainer->remove($this->arguments['as']);
		}
		return $out;
	}
}

?>