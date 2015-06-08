<?php
namespace NamelessCoder\Fluid\Tests\Unit\ViewHelpers\Fixtures;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use NamelessCoder\Fluid\Core\Rendering\RenderingContextInterface;
use NamelessCoder\Fluid\Core\Variables\VariableProviderInterface;
use NamelessCoder\Fluid\Core\ViewHelper\TemplateVariableContainer;

/**
 * Constraint syntax tree node fixture
 */
class ConstraintSyntaxTreeNode extends ViewHelperNode {
	public $callProtocol = array();

	public function __construct(VariableProviderInterface $variableContainer) {
		$this->variableContainer = $variableContainer;
	}

	public function evaluateChildNodes(RenderingContextInterface $renderingContext) {
		$identifiers = (array) $this->variableContainer->getAllIdentifiers();
		$callElement = array();
		foreach ($identifiers as $identifier) {
			$callElement[$identifier] = $this->variableContainer->get($identifier);
		}
		$this->callProtocol[] = $callElement;
	}

	public function evaluate(RenderingContextInterface $renderingContext) {
	}
}
