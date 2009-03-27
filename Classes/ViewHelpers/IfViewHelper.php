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
 * This view helper implements an if/else condition.
 *
 * Example:
 * (1) Basic usage
 *
 * <f3:if condition="somecondition">
 *   This is being shown in case the condition matches
 * </f3:if>
 * Everything inside the <f3:if> tag is being displayed if the condition evaluates to TRUE.
 *
 *
 * (2) If / Then / Else
 *
 * <f3:if condition="somecondition">
 *   <f3:then>
 *     This is being shown in case the condition matches.
 *   </f3:then>
 *   <f3:else>
 *     This is being displayed in case the condition evaluates to FALSE.
 *   </f3:else>
 * </f3:if>
 * Everything inside the "then" tag is displayed if the condition evaluates to TRUE.
 * Otherwise, everything inside the "else"-tag is displayed.
 *
 * TODO:
 * Currently, condition handling is not really implemented.
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 * @todo implement useful conditions
 */
class IfViewHelper extends \F3\Fluid\Core\AbstractViewHelper implements \F3\Fluid\Core\Facets\ChildNodeAccessInterface {

	/**
	 * An array of \F3\Fluid\Core\SyntaxTree\AbstractNode
	 * @var array
	 */
	protected $childNodes;

	/**
	 * Setter for ChildNodes - as defined in ChildNodeAccessInterface
	 *
	 * @param array $childNodes Child nodes of this syntax tree node
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setChildNodes(array $childNodes) {
		$this->childNodes = $childNodes;
	}

	/**
	 * Initialize arguments. We require only the argument "condition"
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
		$this->registerArgument('condition', 'Raw', 'View helper condition', TRUE);
	}

	/**
	 * Render the if.
	 *
	 * @return string the rendered string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @todo More sophisticated condition evaluation
	 */
	public function render() {
		$out = '';

		$conditionResult = ($this->arguments['condition'] == TRUE);
		if (is_object($this->arguments['condition']) && $this->arguments['condition'] instanceof \Countable) {
			$conditionResult = count($this->arguments['condition']) > 0;
		}

		if ($conditionResult === TRUE) {
			foreach ($this->childNodes as $childNode) {
				if ($childNode instanceof \F3\Fluid\Core\SyntaxTree\ViewHelperNode
				    && $childNode->getViewHelperClassName() === 'F3\Fluid\ViewHelpers\ThenViewHelper' ) {
					return $childNode->render($this->variableContainer);
				} else {
					$out .= $childNode->render($this->variableContainer);
				}
			}
		} else {
			foreach ($this->childNodes as $childNode) {
				if ($childNode instanceof \F3\Fluid\Core\SyntaxTree\ViewHelperNode
				    && $childNode->getViewHelperClassName() === 'F3\Fluid\ViewHelpers\ElseViewHelper' ) {
					return $childNode->render($this->variableContainer);
				}
			}
		}
		return $out;
	}
}

?>
