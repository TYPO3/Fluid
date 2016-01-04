<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Declares new variables which are aliases of other variables.
 * Takes a "map"-Parameter which is an associative array which defines the shorthand mapping.
 *
 * The variables are only declared inside the <f:alias>...</f:alias>-tag. After the
 * closing tag, all declared variables are removed again.
 *
 * ### Examples
 *
 * #### Single alias
 *
 * ```html
 * <f:alias map="{x: 'foo'}">{x}</f:alias>
 * ```
 * will output: ```foo```
 *
 * #### Multiple mappings
 *
 * ```html
 * <f:alias map="{x: foo.bar.baz, y: foo.bar.baz.name}">
 *   {x.name} or {y}
 * </f:alias>
 * ```
 * will output ```max or max``` given ```{foo.bar.baz}``` contains and array/object with a key/property ```{name: 'max'}```
 *
 * > Note: Using this view helper can be a sign of weak architecture and can lead to unreadable
 * > fluid templates. Instead of using this extensively you might want to fine-tune your
 * > "view model" (the data you assign to the view).
 *
 * @api
 */
class AliasViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('map', 'array', 'Array that specifies which variables should be mapped to which alias', TRUE);
	}

	/**
	 * Renders alias
	 *
	 * @return string Rendered string
	 * @api
	 */
	public function render() {
		$map = $this->arguments['map'];
		foreach ($map as $aliasName => $value) {
			$this->templateVariableContainer->add($aliasName, $value);
		}
		$output = $this->renderChildren();
		foreach ($map as $aliasName => $value) {
			$this->templateVariableContainer->remove($aliasName);
		}
		return $output;
	}
}
