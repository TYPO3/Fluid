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
 * @package
 * @subpackage
 * @version $Id:$
 */
/**
 * [Enter description here]
 *
 * @package
 * @subpackage
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class SectionViewHelper extends \F3\Fluid\Core\AbstractViewHelper implements \F3\Fluid\Core\Facets\PostParseInterface {

	/**
	 * Initialize the arguments.
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of the section', TRUE);
	}

	/**
	 * Save the associated view helper node in a static public class variable.
	 * called directly after the view helper was built.
	 */
	static public function postParseEvent(\F3\Fluid\Core\SyntaxTree\ViewHelperNode $syntaxTreeNode, $viewHelperArguments, \F3\Fluid\Core\VariableContainer $variableContainer) {
		$sectionName = $viewHelperArguments['name']->render(new \F3\Fluid\Core\VariableContainer());
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
	 */
	public function render() {
		return $this->renderChildren();
	}
}


?>
