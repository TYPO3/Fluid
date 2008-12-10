<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core;

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
 * @version $Id:$
 */
/**
 * This interface is returned by \F3\Fluid\Core\TemplateParser->parse() method.
 *
 * @package Fluid
 * @subpackage Core
 */
interface ParsedTemplateInterface {
	/**
	 * Get root node of this parsing state.
	 *
	 * @return \F3\Fluid\Core\SyntaxTree\RootNode The root node
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getRootNode();
	
	/**
	 * Returns a variable container used in the PostParse Facet.
	 *
	 * @return \F3\Fluid\Core\VariableContainer
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getVariableContainer();
}

?>
