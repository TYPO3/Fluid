<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Form;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package
 * @subpackage
 * @version $Id:$
 */
/**
 * Enter description here...
 * @scope prototype
 */
class UploadViewHelper extends \F3\Fluid\ViewHelpers\Form\AbstractFormViewHelper {

	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerUniversalTagAttributes();
	}
	public function render() {
		return '<input type="file" name="' . $this->getName() . '" ' . $this->renderTagAttributes() . ' />';
	}
}


?>
