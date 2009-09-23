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
 * This view helper implements an ifGrantedAuthority/else condition.
 *
 * = Examples =
 *
 * <code title="Basic usage">
 * <f:security.ifGrantedAuthority grantedAuthority="Administrator">
 *   This is being shown in case you have the Administrator granted authority (aka role).
 * </f:security.ifGrantedAuthority>
 * </code>
 *
 * Everything inside the <f:ifGrantedAuthority> tag is being displayed if you have the given granted authority.
 *
 * <code title="IfGrantedAuthority / then / else">
 * <f:security.ifGrantedAuthority grantedAuthority="Administrator">
 *   <f:then>
 *     This is being shown in case you have the granted authority.
 *   </f:then>
 *   <f:else>
 *     This is being displayed in case you do not have the granted authority.
 *   </f:else>
 * </f:security.ifGrantedAuthority>
 * </code>
 *
 * Everything inside the "then" tag is displayed if you have the role.
 * Otherwise, everything inside the "else"-tag is displayed.
 *
 *
 *
 * @version $Id: IfViewHelper.php 2832 2009-07-17 14:53:19Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class IfGrantedAuthorityViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper implements \F3\Fluid\Core\ViewHelper\Facets\ChildNodeAccessInterface {

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
	 * @var \F3\FLOW3\Security\Context
	 */
	protected $securityContext;

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
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function setRenderingContext(\F3\Fluid\Core\Rendering\RenderingContext $renderingContext) {
		$this->renderingContext = $renderingContext;
	}

	/**
	 * Injects the security context holder and fetches the security context from it
	 *
	 * @param \F3\FLOW3\Security\ContextHolderInterface $securityContextHolder The security context holder
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function injectSecurityContextHolder(\F3\FLOW3\Security\ContextHolderInterface $securityContextHolder) {
		$this->securityContext = $securityContextHolder->getContext();
	}

	/**
	 * renders <f:then> child if the granted authority could be found in the security context,
	 * otherwise renders <f:else> child.
	 *
	 * @param string $grantedAuthority The granted authority
	 * @return string the rendered string
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @api
	 */
	public function render($grantedAuthority) {
		if ($this->securityContext->hasGrantedAuthority($grantedAuthority)) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}

	/**
	 * Iterates through child nodes and renders ThenViewHelper.
	 * If no ThenViewHelper is found, all child nodes are rendered
	 *
	 * @return string rendered ThenViewHelper or contents of <f:if> if no ThenViewHelper was found
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
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
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
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
