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
 * A Section view helper
 *
 * == Examples ==
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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class SectionViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper implements \TYPO3\Fluid\Core\ViewHelper\Facets\PostParseInterface, \TYPO3\Fluid\Core\ViewHelper\Facets\CompilableInterface {

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of the section', TRUE);
	}

	/**
	 * Save the associated view helper node in a static public class variable.
	 * called directly after the view helper was built.
	 *
	 * @param \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode $syntaxTreeNode
	 * @param array $viewHelperArguments
	 * @param \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer $variableContainer
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	static public function postParseEvent(\TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode $syntaxTreeNode, array $viewHelperArguments, \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer $variableContainer) {
		$sectionName = $viewHelperArguments['name']->getText();
		if (!$variableContainer->exists('sections')) {
			$variableContainer->add('sections', array());
		}
		$sections = $variableContainer->get('sections');
		$sections[$sectionName] = $syntaxTreeNode;
		$variableContainer->remove('sections');
		$variableContainer->add('sections', $sections);
	}

	/**
	 * Rendering directly returns all child nodes.
	 *
	 * @return string HTML String of all child nodes.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function render() {
		if ($this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\SectionViewHelper', 'isCurrentlyRenderingSection')) {
			$this->viewHelperVariableContainer->remove('TYPO3\Fluid\ViewHelpers\SectionViewHelper', 'isCurrentlyRenderingSection');
			return $this->renderChildren();
		}
		return '';
	}

	/**
	 * The inner contents of a section should not be rendered.
	 *
	 * @param string $argumentsVariableName
	 * @param string $renderChildrenClosureVariableName
	 * @param string $initializationPhpCode
	 * @param \TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode $syntaxTreeNode
	 * @param \TYPO3\Fluid\Core\Compiler\TemplateCompiler $templateCompiler
	 * @return string
	 */
	public function compile($argumentsVariableName, $renderChildrenClosureVariableName, &$initializationPhpCode, \TYPO3\Fluid\Core\Parser\SyntaxTree\AbstractNode $syntaxTreeNode, \TYPO3\Fluid\Core\Compiler\TemplateCompiler $templateCompiler) {
		return '\'\'';
	}
}

?>
