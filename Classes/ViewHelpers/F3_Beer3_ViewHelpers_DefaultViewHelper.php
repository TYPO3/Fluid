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
 * [Enter description here]
 *
 * @package Beer3
 * @subpackage ViewHelpers
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class DefaultViewHelper {
	public function baseMethod() {}
	public function forMethod(F3::Beer3::NodeInterface $node, $arguments) {
		//if (!array_key_exists('each', $arguments)) throw new F3::Beer3::Exception('Argument "each" not specified in loop.', 1224590686);
		$out = '';
		foreach ($arguments['each'] as $singleElement) {
			$node->addToContext($arguments['as'], $singleElement);
			$out .= $node->renderSubtree();
			$node->removeFromContext($arguments['as']);
		}
		return $out;
	}
}


?>