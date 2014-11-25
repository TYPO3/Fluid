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

use TYPO3\Flow\Utility\Files;

/**
 * Warn about removed ReflectionService dependency from AbstractViewHelper
 */
class Version20141121091700 extends AbstractMigration {

	public function up() {
		$affectedFiles = array();
		$allPathsAndFilenames = Files::readDirectoryRecursively($this->targetPackageData['path'], NULL, TRUE);
		foreach ($allPathsAndFilenames as $pathAndFilename) {
			if (substr($pathAndFilename, -14) !== 'ViewHelper.php') {
				continue;
			}
			$fileContents = file_get_contents($pathAndFilename);
			if (preg_match('/\$this->reflectionService/', $fileContents) === 1) {
				$affectedFiles[] = substr($pathAndFilename, strlen($this->targetPackageData['path']) + 1);
			}
		}

		if ($affectedFiles !== array()) {
			$this->showWarning('Following ViewHelpers might use a removed ReflectionService dependency from AbstractViewHelper, please inject a ReflectionService instance yourself:' . PHP_EOL . PHP_EOL . '* ' . implode(PHP_EOL . '* ', $affectedFiles));
		}
	}

}
