<?php
namespace TYPO3\Fluid\ViewHelpers\Security;

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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class IfAuthenticatedViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractConditionViewHelper {
	/**
	 * @var TYPO3\FLOW3\Security\Context
	 */
	protected $securityContext;

	/**
	 * Injects the Security Context
	 *
	 * @param \TYPO3\FLOW3\Security\Context $securityContext
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectSecurityContext(\TYPO3\FLOW3\Security\Context $securityContext) {
		$this->securityContext = $securityContext;
	}

	/**
	 * Renders <f:then> child if any account is currently authenticated, otherwise renders <f:else> child.
	 *
	 * @return string the rendered string
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function render() {
		$activeTokens = $this->securityContext->getAuthenticationTokens();
		foreach ($activeTokens as $token) {
			if ($token->isAuthenticated()) {
				return $this->renderThenChild();
			}
		}
		return $this->renderElseChild();
	}
}
?>
