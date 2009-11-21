<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Parser\Interceptor;

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
 * An interceptor adding the escape viewhelper to the suitable places.
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Escape implements \F3\Fluid\Core\Parser\InterceptorInterface {

	/**
	 * Inject object factory
	 *
	 * @param \F3\FLOW3\Object\FactoryInterface $objectFactory
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function injectObjectFactory(\F3\FLOW3\Object\FactoryInterface $objectFactory) {
		$this->objectFactory = $objectFactory;
	}

	/**
	 * Adds a ViewHelper node using the EscapeViewHelper to the given node.
	 *
	 * @param \F3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode $node
	 * @return \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function process(\F3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node) {
		if (!($node instanceof \F3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode)) {
			$up = new \InvalidArgumentException(__CLASS__ . ' only handles ObjectAccessorNode instances, ' . get_class($node) . ' was given.', 1258552518);
			throw $up;
		}

		$viewHelper = $this->objectFactory->create('F3\Fluid\ViewHelpers\EscapeViewHelper');
		return $this->objectFactory->create('F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode', $viewHelper, array('value' => $node));
	}

}
?>