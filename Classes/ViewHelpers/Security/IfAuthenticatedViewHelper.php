<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Security;

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
 * This view helper implements an ifAuthenticated/else condition.
 *
 * = Examples =
 *
 * <code title="Basic usage">
 * <f:security.ifAuthenticated>
 *   This is being shown whenever a user is logged in
 * </f:security.ifAuthenticated>
 * </code>
 *
 * Everything inside the <f:ifAuthenticated> tag is being displayed if you are authenticated with any account.
 *
 * <code title="IfAuthenticated / then / else">
 * <f:security.ifAuthenticated>
 *   <f:then>
 *     This is being shown in case you have access.
 *   </f:then>
 *   <f:else>
 *     This is being displayed in case you do not have access.
 *   </f:else>
 * </f:security.ifAuthenticated>
 * </code>
 *
 * Everything inside the "then" tag is displayed if you have access.
 * Otherwise, everything inside the "else"-tag is displayed.
 *
 *
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class IfAuthenticatedViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper implements \F3\Fluid\Core\ViewHelper\Facets\ChildNodeAccessInterface {

	/**
	 * An array of \F3\Fluid\Core\Parser\SyntaxTree\AbstractNode
	 * @var array
	 */
	protected $childNodes = array();

	/**
	 * @var F3\Fluid\Core\Rendering\RenderingContext
	 */
	protected $renderingContext;

	/**
	 * @var F3\FLOW3\Security\ContextHolderInterface
	 */
	protected $securityContextHolder;

	/**
	 * Injects the Security Context Holder
	 *
	 * @param F3\FLOW3\Security\ContextHolderInterface $securityContextHolder
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectSecurityContextHolder(\F3\FLOW3\Security\ContextHolderInterface $securityContextHolder) {
		$this->securityContextHolder = $securityContextHolder;
	}

	/**
	 * Setter for ChildNodes - as defined in ChildNodeAccessInterface
	 *
	 * @param array $childNodes Child nodes of this syntax tree node
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @api
	 */
	public function setChildNodes(array $childNodes) {
		$this->childNodes = $childNodes;
	}

	/**
	 * Sets the rendering context which needs to be passed on to child nodes
	 *
	 * @param F3\Fluid\Core\Rendering\RenderingContext $renderingContext the renderingcontext to use
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setRenderingContext(\F3\Fluid\Core\Rendering\RenderingContext $renderingContext) {
		$this->renderingContext = $renderingContext;
	}

	/**
	 * Renders <f:then> child if any account is currently authenticated, otherwise renders <f:else> child.
	 *
	 * @return string the rendered string
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function render() {
		$activeTokens = $this->securityContextHolder->getContext()->getAuthenticationTokens();
		foreach ($activeTokens as $token) {
			if ($token->isAuthenticated()) {
				return $this->renderThenChild();
			}
		}
		return $this->renderElseChild();
	}

	/**
	 * Iterates through child nodes and renders ThenViewHelper.
	 * If no ThenViewHelper is found, all child nodes are rendered
	 *
	 * @return string rendered ThenViewHelper or contents of <f:if> if no ThenViewHelper was found
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function renderThenChild() {
		foreach ($this->childNodes as $childNode) {
			if ($childNode instanceof \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
				&& $childNode->getViewHelperClassName() === 'F3\Fluid\ViewHelpers\ThenViewHelper') {
				$childNode->setRenderingContext($this->renderingContext);
				$data = $childNode->evaluate();
				return $data;
			}
		}
		return $this->renderChildren();
	}

	/**
	 * Iterates through child nodes and renders ElseViewHelper.
	 *
	 * @return string rendered ElseViewHelper or an empty string if no ThenViewHelper was found
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function renderElseChild() {
		foreach ($this->childNodes as $childNode) {
			if ($childNode instanceof \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
				&& $childNode->getViewHelperClassName() === 'F3\Fluid\ViewHelpers\ElseViewHelper') {
				$childNode->setRenderingContext($this->renderingContext);
				return $childNode->evaluate();
			}
		}
		return '';
	}
}

?>
