<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Parser;

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
 * An interceptor interface. Interceptors are used in the parsing stage to change
 * the syntax tree of a template, e.g. by adding viewhelper nodes.
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
interface InterceptorInterface {

	/**
	 * The interceptor can process the given node at will and must return a node
	 * that will be used in place of the given node.
	 *
	 * @param \F3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node
	 * @return \F3\Fluid\Core\Parser\SyntaxTree\NodeInterface
	 */
	public function process(\F3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node);

}
?>