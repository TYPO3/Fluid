<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers;

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
 * Form view helper. Generates a <form> Tag.
 *
 * Example
 *
 * (1) Basic usage
 *
 * <f3:form action="...">...</f3:form>
 * Outputs an HTML <form> tag which is targeted at the specified action, in the current controller and package.
 * It will submit the form data via a GET request. If you want to change this, use method="post" as an argument.
 *
 *
 * (2) A complex form with a specified encoding type (needed for file uploads)
 *
 * <f3:form action=".." controller="..." package="..." method="post" enctype="multipart/form-data">...</f3:form>
 *
 *
 * (3) A complex form which should render a domain object.
 *
 * <f3:form action="..." name="customer" object="{customer}">
 *   <f3:form.hidden property="id" />
 *   <f3:form.textbox property="name" />
 * </f3:form>
 * This automatically inserts the value of {customer.name} inside the textbox and adjusts the name of the textbox accordingly.
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class FormViewHelper extends \F3\Fluid\Core\TagBasedViewHelper {

	/**
	 * Initialize arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
		$this->registerArgument('action', 'string', 'name of action to call', TRUE);
		$this->registerArgument('controller', 'string', 'name of controller to call the current action on');
		$this->registerArgument('package', 'string', 'name of package to call');
		$this->registerArgument('subpackage', 'string', 'name of subpackage to call');
		$this->registerArgument('object', 'Raw', 'Object to use for the form. Use in conjunction with the "property" attribute on the sub tags.');
		$this->registerArgument('arguments', 'array', 'Associative array of all URL arguments which should be appended to the action URI.');

		$this->registerTagAttribute('enctype', 'string', 'MIME type with which the form is submitted');
		$this->registerTagAttribute('method', 'string', 'Transfer type (GET or POST)');
		$this->registerTagAttribute('name', 'string', 'Name of form');
		$this->registerTagAttribute('onreset', 'string', 'JavaScript: On reset of the form');
		$this->registerTagAttribute('onsubmit', 'string', 'JavaScript: On submit of the form');

		$this->registerUniversalTagAttributes();
	}

	/**
	 * Render the form.
	 *
	 * @return string FORM-Tag.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render() {
		$uriHelper = $this->variableContainer->get('view')->getViewHelper('F3\FLOW3\MVC\View\Helper\URIHelper');

		$method = ( $this->arguments['method'] ? $this->arguments['method'] : 'GET' );

		$formActionUrl = $uriHelper->URIFor($this->arguments['action'], $this->arguments['arguments'], $this->arguments['controller'], $this->arguments['package'], $this->arguments['subpackage'], array());

		$hiddenIdentityFields = '';
		if ($this->arguments['object']) {
			$this->variableContainer->add('__formObject', $this->arguments['object']);
			$hiddenIdentityFields = $this->generateHiddenIdentityFields($this->arguments['object']);
		}
		if ($this->arguments['name']) {
			$this->variableContainer->add('__formName', $this->arguments['name']);
		}

		$out = '<form action="' . $formActionUrl . '" ' . $this->renderTagAttributes() . '>';
		$out .= $hiddenIdentityFields;
		$out .= $this->renderChildren();
		$out .= '</form>';

		if ($this->arguments['object']) {
			$this->variableContainer->remove('__formObject');
		}
		if ($this->arguments['name']) {
			$this->variableContainer->remove('__formName');
		}

		return $out;
	}

	/**
	 *			// <![CDATA[<f:form.hidden name="updatedBlog[__identity][name]" value="{blog.name}" />]]>
	 *
	 */
	protected function generateHiddenIdentityFields($object) {
		if ($this->persistenceManager->getBackend()->isNewObject($object)) return '';
/*
		$classSchema = $this->persistenceManager->getClassSchema($object);
		foreach (array_keys($classSchema->getIdentityProperties()) as $propertyName) {
			$propertyValue = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object, $propertyName);
		}
*/
	}
}

?>
