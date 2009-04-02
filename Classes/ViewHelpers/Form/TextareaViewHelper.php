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
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 */

/**
 * Textarea view helper.
 *
 * The value of the text area needs to be set via the "value" attribute, as with all other form ViewHelpers.
 *
 * <code title="Example">
 * <f:textarea name="myTextArea" value="This is shown inside the textarea" />
 * </code>
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class TextareaViewHelper extends \F3\Fluid\ViewHelpers\Form\AbstractFormViewHelper {

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Renders the textarea.
	 *
	 * @return string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render() {
		$out = '<textarea name="' . $this->getName() . '"' . $this->renderTagAttributes() . '>';
		$out .= $this->getValue();
		$out .= '</textarea>';
		return $out;
	}
}

?>