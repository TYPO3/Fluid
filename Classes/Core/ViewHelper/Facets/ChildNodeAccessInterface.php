<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\ViewHelper\Facets;

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
 * Child Node Access Facet. View Helpers should implement this interface if they need access to the direct children in the Syntax Tree at rendering-time.
 * This might happen if you only want to selectively render a part of the syntax tree depending on some conditions.
 *
 * In most cases, you will not need this view helper.
 *
 * See \F3\Fluid\ViewHelpers\IfViewHelper for an example how it is used.
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @internal
 */
interface ChildNodeAccessInterface {

	/**
	 * Sets the direct child nodes of the current syntax tree node.
	 *
	 * @param array<\F3\Fluid\Core\Parser\SyntaxTree\AbstractNode> $childNodes
	 * @return void
	 */
	public function setChildNodes(array $childNodes);

	/**
	 * Sets the rendering context which needs to be passed on to child nodes
	 *
	 * @param F3\Fluid\Core\Rendering\RenderingContext $renderingContext the renderingcontext to use
	 * @return void
	 */
	public function setRenderingContext(\F3\Fluid\Core\Rendering\RenderingContext $renderingContext);

}

?>