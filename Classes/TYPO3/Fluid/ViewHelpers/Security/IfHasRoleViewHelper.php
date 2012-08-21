<?php
namespace TYPO3\Fluid\ViewHelpers\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * This view helper implements an ifHasRole/else condition.
 *
 * = Examples =
 *
 * <code title="Basic usage">
 * <f:security.ifHasRole role="Administrator">
 *   This is being shown in case you have the Administrator role (aka role) defined in the
 *   current package according to the controllerContext
 * </f:security.ifHasRole>
 * </code>
 *
 * <code title="Usage with packageKey attribute">
 * <f:security.ifHasRole role="Administrator" packageKey="Acme.MyPackage">
 *   This is being shown in case you have the Acme.MyPackage:Administrator role (aka role).
 * </f:security.ifHasRole>
 * </code>
 *
 * <code title="Usage with full role identifier in role attribute">
 * <f:security.ifHasRole role="Acme.MyPackage:Administrator">
 *   This is being shown in case you have the Acme.MyPackage:Administrator role (aka role).
 * </f:security.ifHasRole>
 * </code>
 *
 * Everything inside the <f:ifHasRole> tag is being displayed if you have the given role.
 *
 * <code title="IfRole / then / else">
 * <f:security.ifHasRole role="Administrator">
 *   <f:then>
 *     This is being shown in case you have the role.
 *   </f:then>
 *   <f:else>
 *     This is being displayed in case you do not have the role.
 *   </f:else>
 * </f:security.ifHasRole>
 * </code>
 *
 * Everything inside the "then" tag is displayed if you have the role.
 * Otherwise, everything inside the "else"-tag is displayed.
 *
 * @api
 */
class IfHasRoleViewHelper extends AbstractConditionViewHelper {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Context
	 */
	protected $securityContext;

	/**
	 * renders <f:then> child if the role could be found in the security context,
	 * otherwise renders <f:else> child.
	 *
	 * @param string $role The role
	 * @param string $packageKey PackageKey of the package defining the role
	 * @return string the rendered string
	 * @api
	 */
	public function render($role, $packageKey = NULL) {
		if ($role !== 'Everybody' && $role !== 'Anonymous' && strpos($role, '.') === FALSE && strpos($role, ':') === FALSE) {
			if ($packageKey === NULL) {
				$role = $this->controllerContext->getRequest()->getControllerPackageKey() . ':' . $role;
			} else {
				$role = $packageKey . ':' . $role;
			}
		}

		if ($this->securityContext->hasRole($role)) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}
}
?>
