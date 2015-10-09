<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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
		$this->registerArgument('default', 'mixed', 'Value (usually string) to be displayed if the section or partial does not exist', FALSE, NULL);
		$this->registerArgument('contentAs', 'string', 'If used, renders the child content and adds it as a template variable with this name for use in the partial/section', FALSE, NULL);
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
		$contentAs = $this->arguments['contentAs'];
		$tagContent = $this->renderChildren();

		if ($contentAs !== NULL) {
			$arguments[$contentAs] = $tagContent;
		}

		$content = '';
		if ($partial !== NULL) {
			$content = $this->viewHelperVariableContainer->getView()->renderPartial($partial, $section, $arguments, $optional);
		} elseif ($section !== NULL) {
			$content = $this->viewHelperVariableContainer->getView()->renderSection($section, $arguments, $optional);
		}
		// Replace empty content with default value. If default is
		// not set, NULL is returned and cast to a new, empty string
		// outside of this ViewHelper.
		if ($content === '') {
			$content = isset($this->arguments['default']) ? $this->arguments['default'] : $tagContent;
		}
		return $content;
	}

}
