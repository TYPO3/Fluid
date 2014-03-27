<?php
namespace TYPO3\Fluid\ViewHelpers\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\Authorization\PrivilegeManagerInterface;
use TYPO3\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * This view helper implements an ifAccess/else condition.
 *
 * = Examples =
 *
 * <code title="Basic usage">
 * <f:security.ifAccess privilegeTarget="somePrivilegeTargetIdentifier">
 *   This is being shown in case you have access to the given privilege
 * </f:security.ifAccess>
 * </code>
 *
 * Everything inside the <f:ifAccess> tag is being displayed if you have access to the given privilege.
 *
 * <code title="IfAccess / then / else">
 * <f:security.ifAccess privilegeTarget="somePrivilegeTargetIdentifier">
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
 * <code title="Inline syntax with privilege parameters">
 * {f:security.ifAccess(privilegeTarget: 'someTarget', parameters: '{param1: \'value1\'}', then: 'has access', else: 'has no access')}
 * </code>
 *
 * @api
 */
class IfAccessViewHelper extends AbstractConditionViewHelper {

	/**
	 * @Flow\Inject
	 * @var PrivilegeManagerInterface
	 */
	protected $privilegeManager;

	/**
	 * renders <f:then> child if access to the given resource is allowed, otherwise renders <f:else> child.
	 *
	 * @param string $privilegeTarget The Privilege target identifier
	 * @param array $parameters optional privilege target parameters to be evaluated
	 * @return string the rendered then/else child nodes depending on the access
	 * @api
	 */
	public function render($privilegeTarget, array $parameters = array()) {
		if ($this->privilegeManager->isPrivilegeTargetGranted($privilegeTarget, $parameters)) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}
}
