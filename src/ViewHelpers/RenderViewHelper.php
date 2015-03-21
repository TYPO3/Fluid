<?php
namespace TYPO3\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * A ViewHelper to render a section or a specified partial in a template.
 *
 * = Examples =
 *
 * <code title="Rendering partials">
 * <f:render partial="SomePartial" arguments="{foo: someVariable}" />
 * </code>
 * <output>
 * the content of the partial "SomePartial". The content of the variable {someVariable} will be available in the partial as {foo}
 * </output>
 *
 * <code title="Rendering sections">
 * <f:section name="someSection">This is a section. {foo}</f:section>
 * <f:render section="someSection" arguments="{foo: someVariable}" />
 * </code>
 * <output>
 * the content of the section "someSection". The content of the variable {someVariable} will be available in the partial as {foo}
 * </output>
 *
 * <code title="Rendering recursive sections">
 * <f:section name="mySection">
 *  <ul>
 *    <f:for each="{myMenu}" as="menuItem">
 *      <li>
 *        {menuItem.text}
 *        <f:if condition="{menuItem.subItems}">
 *          <f:render section="mySection" arguments="{myMenu: menuItem.subItems}" />
 *        </f:if>
 *      </li>
 *    </f:for>
 *  </ul>
 * </f:section>
 * <f:render section="mySection" arguments="{myMenu: menu}" />
 * </code>
 * <output>
 * <ul>
 *   <li>menu1
 *     <ul>
 *       <li>menu1a</li>
 *       <li>menu1b</li>
 *     </ul>
 *   </li>
 * [...]
 * (depending on the value of {menu})
 * </output>
 *
 *
 * <code title="Passing all variables to a partial">
 * <f:render partial="somePartial" arguments="{_all}" />
 * </code>
 * <output>
 * the content of the partial "somePartial".
 * Using the reserved keyword "_all", all available variables will be passed along to the partial
 * </output>
 *
 * @api
 */
class RenderViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('section', 'string', 'Section to render - combine with partial to render section in partial', FALSE, NULL);
		$this->registerArgument('partial', 'string', 'Partial to render, with or without section', FALSE, NULL);
		$this->registerArgument('arguments', 'array', 'Array of variables to be transferred. Use {_all} for all variables', FALSE, array());
		$this->registerArgument('optional', 'boolean', 'If TRUE, considers the *section* optional. Partial never is.', FALSE, FALSE);
	}

	/**
	 * Renders the content.
	 *
	 * @return string
	 * @api
	 */
	public function render() {
		$section = $this->arguments['section'];
		$partial = $this->arguments['partial'];
		$arguments = (array) $this->arguments['arguments'];
		$optional = (boolean) $this->arguments['optional'];
		if ($partial !== NULL) {
			return $this->viewHelperVariableContainer->getView()->renderPartial($partial, $section, $arguments, $optional);
		} elseif ($section !== NULL) {
			return $this->viewHelperVariableContainer->getView()->renderSection($section, $arguments, $optional);
		}
		return '';
	}

}
