<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Parser\SyntaxTree;

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
 * @subpackage Core
 * @version $Id$
 */

/**
 * Root node of every syntax tree.
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 * @internal
 */
class RootNode extends \F3\Fluid\Core\Parser\SyntaxTree\AbstractNode {

	/**
	 * Evaluate the root node, by evaluating the subtree.
	 *
	 * @return object Evaluated subtree
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	public function evaluate() {
		if ($this->renderingContext === NULL) {
			throw new \F3\Fluid\Core\RuntimeException('Rendering Context is null in RootNode, but necessary. If this error appears, please report a bug!', 1242669004);
		}
		$text = $this->evaluateChildNodes();
		return $text;
	}
}

?>