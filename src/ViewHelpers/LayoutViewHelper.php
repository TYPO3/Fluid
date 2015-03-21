<?php
namespace TYPO3\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper\PostParseInterface;
use TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer;

/**
 * With this tag, you can select a layout to be used for the current template.
 *
 * = Examples =
 *
 * <code>
 * <f:layout name="main" />
 * </code>
 * <output>
 * (no output)
 * </output>
 *
 * @api
 */
class LayoutViewHelper extends AbstractViewHelper {

	/**
	 * Initialize arguments
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of layout to use. If none given, "Default" is used.');
	}

	/**
	 * On the post parse event, add the "layoutName" variable to the variable container so it can be used by the TemplateView.
	 *
	 * @param ViewHelperNode $syntaxTreeNode
	 * @param array $viewHelperArguments
	 * @param TemplateVariableContainer $variableContainer
	 * @return void
	 */
	static public function postParseEvent(ViewHelperNode $syntaxTreeNode, array $viewHelperArguments, TemplateVariableContainer $variableContainer) {
		if (isset($viewHelperArguments['name'])) {
			$layoutNameNode = $viewHelperArguments['name'];
		} else {
			$layoutNameNode = new TextNode('Default');
		}

		$variableContainer->add('layoutName', $layoutNameNode);
	}

	/**
	 * This tag will not be rendered at all.
	 *
	 * @return void
	 * @api
	 */
	public function render() {
	}
}
