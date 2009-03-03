<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\SyntaxTree;

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
 * @subpackage Core
 * @version $Id$
 */

/**
 * Text Syntax Tree Node - is a container for strings.
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class TextNode extends \F3\Fluid\Core\SyntaxTree\AbstractNode {

	/**
	 * Contents of the text node
	 * @var string
	 */
	protected $text;

	/**
	 * Constructor.
	 *
	 * @param string $text text to store in this textNode
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function __construct($text) {
		if (!is_string($text)) throw new \F3\Fluid\Core\ParsingException('Text node requires an argument of type string, "' . gettype($text) . '" given.');
		$this->text = $text;
	}

	/**
	 * Return the text associated to the syntax tree.
	 *
	 * @param \F3\Fluid\Core\VariableContainer $variableContainer Variable Container where all variables are stored in
	 * @return string the text stored in this node.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluate(\F3\Fluid\Core\VariableContainer $variableContainer) {
		return $this->text;
	}
}


?>