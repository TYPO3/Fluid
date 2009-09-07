<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers;

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
 * Form view helper. Generates a <form> Tag.
 *
 * = Basic usage =
 *
 * Use <f:form> to output an HTML <form> tag which is targeted at the specified action, in the current controller and package.
 * It will submit the form data via a POST request. If you want to change this, use method="get" as an argument.
 * <code title="Example">
 * <f:form action="...">...</f:form>
 * </code>
 *
 * = A complex form with a specified encoding type =
 *
 * <code title="Form with enctype set">
 * <f:form action=".." controllerName="..." packageName="..." enctype="multipart/form-data">...</f:form>
 * </code>
 *
 * = A Form which should render a domain object =
 *
 * <code title="Binding a domain object to a form">
 * <f:form action="..." name="customer" object="{customer}">
 *   <f:form.hidden property="id" />
 *   <f:form.textbox property="name" />
 * </f:form>
 * </code>
 * This automatically inserts the value of {customer.name} inside the textbox and adjusts the name of the textbox accordingly.
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class FormViewHelper extends \F3\Fluid\ViewHelpers\Form\AbstractFormViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'form';

	/**
	 * Initialize arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
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
	 * @param string $action target action
	 * @param array $arguments additional arguments
	 * @param string $controller name of target controller
	 * @param string $package name of target package
	 * @param string $subpackage name of target subpackage
	 * @param mixed $object object to use for the form. Use in conjunction with the "property" attribute on the sub tags
	 * @param string $section The anchor to be added to the URI
	 * @param string $fieldNamePrefix Prefix that will be added to all field names within this form
	 * @param string $actionUri can be used to overwrite the "action" attribute of the form tag
	 * @return string rendered form
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @api
	 */
	public function render($action = '', array $arguments = array(), $controller = NULL, $package = NULL, $subpackage = NULL, $object = NULL, $section = '', $fieldNamePrefix = NULL, $actionUri = NULL) {
		$this->setFormActionUri();

		if (strtolower($this->arguments['method']) === 'get') {
			$this->tag->addAttribute('method', 'get');
		} else {
			$this->tag->addAttribute('method', 'post');
		}

		$this->addFormNameToViewHelperVariableContainer();
		$this->addFormObjectToViewHelperVariableContainer();
		$this->addFieldNamePrefixToViewHelperVariableContainer();

		$content = $this->renderHiddenIdentityField();
		$content .= $this->renderHiddenReferrerFields();
		$content .= $this->renderChildren();
		$this->tag->setContent($content);

		$this->removeFieldNamePrefixFromViewHelperVariableContainer();
		$this->removeFormObjectFromViewHelperVariableContainer();
		$this->removeFormNameFromViewHelperVariableContainer();

		return $this->tag->render();
	}

	/**
	 * Sets the "action" attribute of the form tag
	 *
	 * @return void
	 */
	protected function setFormActionUri() {
		if ($this->arguments->hasArgument('actionUri')) {
			$formActionUri = $this->arguments['actionUri'];
		} else {
			$uriBuilder = $this->controllerContext->getUriBuilder();
			$formActionUri = $uriBuilder
				->reset()
				->uriFor($this->arguments['action'], $this->arguments['arguments'], $this->arguments['controller'], $this->arguments['package'], $this->arguments['subpackage']);
		}
		$this->tag->addAttribute('action', $formActionUri);
	}

	/**
	 * Renders a hidden form field containing the technical identity of the given object.
	 *
	 * @return string A hidden field containing the Identity (UUID in FLOW3, uid in Extbase) of the given object or NULL if the object is unknown to the persistence framework
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @see \F3\FLOW3\MVC\Controller\Argument::setValue()
	 */
	protected function renderHiddenIdentityField() {
		$object = $this->arguments['object'];
		if (!is_object($object)
			|| !$object instanceof \F3\FLOW3\Persistence\Aspect\DirtyMonitoringInterface
			|| ($object->FLOW3_Persistence_isNew() && !$object->FLOW3_Persistence_isClone())
			){
			return '';
		}
		$identifier = $this->persistenceManager->getBackend()->getIdentifierByObject($object);
		if ($identifier === NULL) {
			return chr(10) . '<!-- Object of type ' . get_class($object) . ' is without identity -->' . chr(10);
		}
		return chr(10) . '<input type="hidden" name="'. $this->prefixFieldName($this->arguments['name']) . '[__identity]" value="' . $identifier .'" />' . chr(10);
	}

	/**
	 * Renders hidden form fields for referrer information about
	 * the current controller and action.
	 *
	 * @return string Hidden fields with referrer information
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @todo filter out referrer information that is equal to the target (e.g. same packageKey)
	 */
	protected function renderHiddenReferrerFields() {
		$request = $this->controllerContext->getRequest();
		$packageKey = $request->getControllerPackageKey();
		$subpackageKey = $request->getControllerSubpackageKey();
		$controllerName = $request->getControllerName();
		$actionName = $request->getControllerActionName();
		$result = chr(10);
		$result .= '<input type="hidden" name="' . $this->prefixFieldName('__referrer[packageKey]') . '" value="' . $packageKey . '" />' . chr(10);
		$result .= '<input type="hidden" name="' . $this->prefixFieldName('__referrer[subpackageKey]') . '" value="' . $subpackageKey . '" />' . chr(10);
		$result .= '<input type="hidden" name="' . $this->prefixFieldName('__referrer[controllerName]') . '" value="' . $controllerName . '" />' . chr(10);
		$result .= '<input type="hidden" name="' . $this->prefixFieldName('__referrer[actionName]') . '" value="' . $actionName . '" />' . chr(10);
		return $result;
	}

	/**
	 * Adds the form name to the ViewHelperVariableContainer if the name attribute is specified.
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function addFormNameToViewHelperVariableContainer() {
		if ($this->arguments->hasArgument('name')) {
			$this->viewHelperVariableContainer->add('F3\Fluid\ViewHelpers\FormViewHelper', 'formName', $this->arguments['name']);
		}
	}

	/**
	 * Removes the form name from the ViewHelperVariableContainer.
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function removeFormNameFromViewHelperVariableContainer() {
		if ($this->arguments->hasArgument('name')) {
			$this->viewHelperVariableContainer->remove('F3\Fluid\ViewHelpers\FormViewHelper', 'formName');
		}
	}

	/**
	 * Adds the object that is bound to this form to the ViewHelperVariableContainer if the formObject attribute is specified.
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function addFormObjectToViewHelperVariableContainer() {
		if ($this->arguments->hasArgument('object')) {
			$this->viewHelperVariableContainer->add('F3\Fluid\ViewHelpers\FormViewHelper', 'formObject', $this->arguments['object']);
		}
	}

	/**
	 * Removes the form object from the ViewHelperVariableContainer.
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function removeFormObjectFromViewHelperVariableContainer() {
		if ($this->arguments->hasArgument('object')) {
			$this->viewHelperVariableContainer->remove('F3\Fluid\ViewHelpers\FormViewHelper', 'formObject');
		}
	}

	/**
	 * Adds the field name prefix to the ViewHelperVariableContainer
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function addFieldNamePrefixToViewHelperVariableContainer() {
		$fieldNamePrefix = '';
		if ($this->arguments->hasArgument('fieldNamePrefix')) {
			$fieldNamePrefix = $this->arguments['fieldNamePrefix'];
		}
		$this->viewHelperVariableContainer->add('F3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix', $fieldNamePrefix);
	}

	/**
	 * Removes field name prefix from the ViewHelperVariableContainer
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function removeFieldNamePrefixFromViewHelperVariableContainer() {
		$this->viewHelperVariableContainer->remove('F3\Fluid\ViewHelpers\FormViewHelper', 'fieldNamePrefix');
	}


}

?>