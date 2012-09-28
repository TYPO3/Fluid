<?php
namespace TYPO3\Flow\Core\Migrations;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Rename form.textbox to form.textfield
 */
class Version201205031303 extends AbstractMigration {

	public function up() {
		$this->searchAndReplace('form.textbox', 'form.textfield', array('html'));

		$this->showNote('Widget configuration has changed, you might want to add "widgetId" attributes to your widget inclusions in Fluid templates. Adjust Routes.yaml as needed!');
	}

}

?>