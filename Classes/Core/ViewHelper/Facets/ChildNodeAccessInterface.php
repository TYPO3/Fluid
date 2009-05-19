<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\ViewHelper\Facets;

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
 * Child Node Access Facet. View Helpers should implement this interface if they need access to the direct children in the Syntax Tree at rendering-time.
 * This might happen if you only want to selectively render a part of the syntax tree depending on some conditions.
 *
 * In most cases, you will not need this view helper.
 *
 * See \F3\Fluid\ViewHelpers\IfViewHelper for an example how it is used.
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @internal
 */
interface ChildNodeAccessInterface {

	/**
	 * Sets the direct child nodes of the current syntax tree node.
	 *
	 * @param array \F3\Fluid\Core\Parser\SyntaxTree\AbstractNode $childNodes
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @internal
	 */
	public function setChildNodes(array $childNodes);
	
	/**
	 * Sets the rendering context which needs to be passed on to child nodes
	 * 
	 * @param F3\Fluid\Core\RenderingContext $renderingContext the renderingcontext to use
	 * @internal
	 */
	public function setRenderingContext(\F3\Fluid\Core\RenderingContext $renderingContext);

}

?>