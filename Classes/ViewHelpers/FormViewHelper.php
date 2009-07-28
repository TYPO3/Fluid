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
class FormViewHelper extends \F3\Fluid\Core\ViewHelper\TagBasedViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'form';

	/**
	 * @var \F3\FLOW3\Persistence\ManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Injects the Persistence Manager
	 *
	 * @param \F3\FLOW3\Persistence\ManagerInterface $persistenceManager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectPersistenceManager(\F3\FLOW3\Persistence\ManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

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
	 * @param string $controllerName target controller
	 * @param string $packageName target package
	 * @param string $subpackageName target subpackage
	 * @param mixed $object object to use for the form. Use in conjunction with the "property" attribute on the sub tags
	 * @param string $section The anchor to be added to the URI
	 * @return string rendered form
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @api
	 */
	public function render($action = '', array $arguments = array(), $controllerName = NULL, $packageName = NULL, $subpackageName = NULL, $object = NULL, $section = '') {
		$uriBuilder = $this->controllerContext->getURIBuilder();
		$formActionUri = $uriBuilder->URIFor($action, $arguments, $controllerName, $packageName, $subpackageName, $section);
		$this->tag->addAttribute('action', $formActionUri);

		if (strtolower($this->arguments['method']) === 'get') {
			$this->tag->addAttribute('method', 'get');
		} else {
			$this->tag->addAttribute('method', 'post');
		}

		if ($this->arguments['name']) {
			$this->viewHelperVariableContainer->add('F3\Fluid\ViewHelpers\FormViewHelper', 'formName', $this->arguments['name']);
		}
		$hiddenIdentityFields = '';
		if (!empty($object)) {
			$this->viewHelperVariableContainer->add('F3\Fluid\ViewHelpers\FormViewHelper', 'formObject', $object);
			$hiddenIdentityFields = $this->renderHiddenIdentityField($object);
		}

		$content = $hiddenIdentityFields;
		$content .= $this->renderHiddenReferrerFields();
		$content .= $this->renderChildren();
		$this->tag->setContent($content);

		if (!empty($object)) {
			$this->viewHelperVariableContainer->remove('F3\Fluid\ViewHelpers\FormViewHelper', 'formObject');
		}
		if ($this->arguments['name']) {
			$this->viewHelperVariableContainer->remove('F3\Fluid\ViewHelpers\FormViewHelper', 'formName');
		}

		return $this->tag->render();
	}

	/**
	 * Renders a hidden form field containing the technical identity of the given object.
	 *
	 * @param object $object The object to create an identity field for
	 * @return string A hidden field containing the Identity (UUID in FLOW3, uid in Extbase) of the given object or NULL if the object is unknown to the persistence framework
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @see \F3\FLOW3\MVC\Controller\Argument::setValue()
	 */
	protected function renderHiddenIdentityField($object) {
		if (!is_object($object) || $this->persistenceManager->getBackend()->isNewObject($object)) {
			return '';
		}
		$identifier = $this->persistenceManager->getBackend()->getIdentifierByObject($object);
		return ($identifier === NULL) ? '<!-- Object of type ' . get_class($object) . ' is without identity -->' : '<input type="hidden" name="'. $this->arguments['name'] . '[__identity]" value="' . $identifier .'" />';
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
		$controllerActionName = $request->getControllerActionName();
		$result = '';
		foreach (array('__referrer[packageKey]' => $packageKey, '__referrer[subpackageKey]' => $subpackageKey, '__referrer[controllerName]' => $controllerName, '__referrer[actionName]' => $controllerActionName) as $fieldName => $fieldValue) {
			$result .= PHP_EOL . '<input type="hidden" name="' . $fieldName . '" value="' . $fieldValue . '" />';
		}
		return $result;
	}
}

?>