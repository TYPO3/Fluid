<?php
namespace TYPO3\Flow\Core\Migrations;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Rename the "resource" argument of the security.ifAccess ViewHelper to "privilegeTarget"
 */
class Version20141113120800 extends AbstractMigration {

	public function up() {
		$this->searchAndReplaceRegex('/\<f\:security\.ifAccess\s+(resource=)/', '<f:security.ifAccess privilegeTarget=', array('html'));
		$this->searchAndReplaceRegex('/\{f\:security\.ifAccess\s*\(\s*(resource:)/', '{f:security.ifAccess(privilegeTarget:', array('html'));
	}

}
