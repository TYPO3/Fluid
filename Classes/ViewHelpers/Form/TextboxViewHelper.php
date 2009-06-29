<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Form;

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
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 */

/**
 * View Helper which creates a simple Text Box (<input type="text">).
 *
  * = Examples =
 *
 * <code title="Example">
 * <f:textbox name="myTextBox" value="default value" />
 * </code>
 *
 * Output:
 * <input type="text" name="myTextBox" value="default value" />
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
use F3\Fluid\Core\ViewHelper;

class TextboxViewHelper extends \F3\Fluid\ViewHelpers\Form\AbstractFormViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'input';

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerTagAttribute('disabled', 'string', 'The disabled attribute of the input field');
		$this->registerTagAttribute('maxlength', 'int', 'The maxlength attribute of the input field (will not be validated)');
		$this->registerTagAttribute('readonly', 'string', 'The readonly attribute of the input field');
		$this->registerTagAttribute('size', 'int', 'The size of the input field');
		$this->registerUniversalTagAttributes();
		$this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
	}

	/**
	 * Renders the textbox.
	 *
	 * @return string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render() {
		$this->tag->addAttribute('type', 'text');
		$this->tag->addAttribute('name', $this->getName());
		$this->tag->addAttribute('value', $this->getValue());

		$this->addErrorStyleClass();

		return $this->tag->render();
	}

	/**
	 * Add an CSS class if this view helper has errors
	 *
	 * @return void
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	protected function addErrorStyleClass() {
		if ($this->arguments->hasArgument('class')) {
			$styleClass = $this->arguments['class'] . ' ';
		} else {
			$styleClass = '';
		}
		$errors = $this->getErrorsForProperty();
		if (count($errors) > 0) {
			$styleClass .= $this->arguments['errorClass'];
			$this->tag->addAttribute('class', $styleClass);
		}
	}
}

?>