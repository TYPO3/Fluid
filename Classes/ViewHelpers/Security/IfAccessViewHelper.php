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
 * This view helper implements an ifAccess/else condition.
 *
 * = Examples =
 *
 * <code title="Basic usage">
 * <f:security.ifAccess resource="someResource">
 *   This is being shown in case you have access to the given resource
 * </f:security.ifAccess>
 * </code>
 *
 * Everything inside the <f:ifAccess> tag is being displayed if you have access to the given resource.
 *
 * <code title="IfAccess / then / else">
 * <f:security.ifAccess resource="someResource">
 *   <f:then>
 *     This is being shown in case you have access.
 *   </f:then>
 *   <f:else>
 *     This is being displayed in case you do not have access.
 *   </f:else>
 * </f:security.ifAccess>
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
class IfAccessViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * @var TYPO3\FLOW3\Security\Authorization\AccessDecisionManagerInterface
	 */
	protected $accessDecisionManager;

	/**
	 * Injects the access decision manager
	 *
	 * @param \TYPO3\FLOW3\Security\Authorization\AccessDecisionManagerInterface $accessDecisionManager The access decision manager
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function injectAccessDecisionManager(\TYPO3\FLOW3\Security\Authorization\AccessDecisionManagerInterface $accessDecisionManager) {
		$this->accessDecisionManager = $accessDecisionManager;
	}

	/**
	 * renders <f:then> child if access to the given resource is allowed, otherwise renders <f:else> child.
	 *
	 * @param string $resource Policy resource
	 * @return string the rendered string
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @api
	 */
	public function render($resource) {
		if ($this->hasAccessToResource($resource)) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}

	/**
	 * Check if we currently have access to the given resource
	 *
	 * @param string $resource The resource to check
	 * @return boolean TRUE if we currently have access to the given resource
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	protected function hasAccessToResource($resource) {
		try {
			$this->accessDecisionManager->decideOnResource($resource);
		} catch (\TYPO3\FLOW3\Security\Exception\AccessDeniedException $e) {
			return FALSE;
		}

		return TRUE;
	}
}

?>
