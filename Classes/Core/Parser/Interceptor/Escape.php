<?php
namespace TYPO3\Fluid\Core\Parser\Interceptor;

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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Escape implements \TYPO3\Fluid\Core\Parser\InterceptorInterface {

	/**
	 * Is the interceptor enabled right now?
	 * @var boolean
	 */
	protected $interceptorEnabled = TRUE;

	/**
	 * A stack of ViewHelperNodes which currently disable the interceptor.
	 * Needed to enable the interceptor again.
	 *
	 * @var array<\TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface>
	 */
	protected $viewHelperNodesWhichDisableTheInterceptor = array();

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Inject object manager
	 *
	 * @param \TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function injectObjectManager(\TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Adds a ViewHelper node using the Format\HtmlspecialcharsViewHelper to the given node.
	 * If "escapingInterceptorEnabled" in the ViewHelper is FALSE, will disable itself inside the ViewHelpers body.
	 *
	 * @param \TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node
	 * @param integer $interceptorPosition One of the INTERCEPT_* constants for the current interception point
	 * @param \TYPO3\Fluid\Core\Parser\ParsingState $parsingState the current parsing state. Not needed in this interceptor.
	 * @return \TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function process(\TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node, $interceptorPosition, \TYPO3\Fluid\Core\Parser\ParsingState $parsingState) {
		if ($interceptorPosition === \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER) {
			if (!$node->getUninitializedViewHelper()->isEscapingInterceptorEnabled()) {
				$this->interceptorEnabled = FALSE;
				$this->viewHelperNodesWhichDisableTheInterceptor[] = $node;
			}
		} elseif ($interceptorPosition === \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER) {
			if (end($this->viewHelperNodesWhichDisableTheInterceptor) === $node) {
				array_pop($this->viewHelperNodesWhichDisableTheInterceptor);
				if (count($this->viewHelperNodesWhichDisableTheInterceptor) === 0) {
					$this->interceptorEnabled = TRUE;
				}
			}
		} elseif ($this->interceptorEnabled && $node instanceof \TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode) {
			$escapeViewHelper = $this->objectManager->get('TYPO3\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper');
			$node = $this->objectManager->create(
				'TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode',
				$escapeViewHelper,
				array('value' => $node)
			);
		}
		return $node;
	}

	/**
	 * This interceptor wants to hook into object accessor creation, and opening / closing ViewHelpers.
	 *
	 * @return array Array of INTERCEPT_* constants
	 */
	public function getInterceptionPoints() {
		return array(
			\TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER,
			\TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER,
			\TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_OBJECTACCESSOR
		);
	}
}
?>