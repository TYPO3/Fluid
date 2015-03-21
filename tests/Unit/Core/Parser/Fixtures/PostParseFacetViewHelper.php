<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Parser\Fixtures;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper\Facets\PostParseInterface;
use TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer;

/**
 * Enter description here...
 */
class PostParseFacetViewHelper extends AbstractViewHelper implements PostParseInterface {

	static public $wasCalled = FALSE;

	public function __construct() {
	}

	static public function postParseEvent(ViewHelperNode $viewHelperNode, array $arguments, TemplateVariableContainer $variableContainer) {
		self::$wasCalled = TRUE;
	}

	public function initializeArguments() {
	}

	public function render() {
	}
}
