<?php
namespace TYPO3\Fluid\ViewHelpers;

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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class LayoutViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper implements \TYPO3\Fluid\Core\ViewHelper\Facets\PostParseInterface {

	/**
	 * Initialize arguments
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of layout to use. If none given, "Default" is used.', TRUE);
	}

	/**
	 * On the post parse event, add the "layoutName" variable to the variable container so it can be used by the TemplateView.
	 *
	 * @param \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode $syntaxTreeNode
	 * @param array $viewHelperArguments
	 * @param \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer $variableContainer
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	static public function postParseEvent(\TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode $syntaxTreeNode, array $viewHelperArguments, \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer $variableContainer) {
		if (isset($viewHelperArguments['name'])) {
			$layoutNameNode = $viewHelperArguments['name'];
		} else {
			$layoutNameNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode('Default');
		}

		$variableContainer->add('layoutName', $layoutNameNode);
	}

	/**
	 * This tag will not be rendered at all.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function render() {
	}
}

?>
