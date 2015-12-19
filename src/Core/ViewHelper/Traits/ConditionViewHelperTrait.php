<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

/*
 * This file is part of the FluidTYPO3/Vhs project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This trait can be used by viewhelpers that generate image tags
 * to add srcsets based to the imagetag for better responsiveness
 */
trait ConditionViewHelperTrait {

	/**
	 * @var NodeInterface
	 */
	protected $childNodes;

	/**
	 * @var array
	 */
	protected $arguments;

	/**
	 * @var RenderingContextInterface
	 */
	protected $renderingContext;

	/**
	 * @param array $childNodes
	 * @return void
	 */
	public function setChildNodes(array $childNodes) {
		$this->childNodes = $childNodes;
	}

	/**
	 * @param array $arguments
	 * @return void
	 */
	public function setArguments(array $arguments) {
		$this->arguments = $arguments;
	}

	/**
	 * Initializes the "then" and "else" arguments
	 */
	public function registerConditionArguments() {
		$this->registerArgument('then', 'mixed', 'Value to be returned if the condition if met.', FALSE);
		$this->registerArgument('else', 'mixed', 'Value to be returned if the condition if not met.', FALSE);
	}

	/**
	 * renders <f:then> child if $condition is true, otherwise renders <f:else> child.
	 *
	 * @return string the rendered string
	 * @api
	 */
	public function render() {
		if (static::evaluateCondition($this->arguments, $this->renderingContext)) {
			return $this->renderThenChild();
		}
		return $this->renderElseChild();
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return mixed
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		if (static::evaluateCondition($arguments, $renderingContext)) {
			if (isset($arguments['then'])) {
				return $arguments['then'];
			}
			if (isset($arguments['__thenClosure'])) {
				return $arguments['__thenClosure']();
			}
		} elseif (!empty($arguments['__elseClosures'])) {
			$elseIfClosures = isset($arguments['__elseifClosures']) ? $arguments['__elseifClosures'] : array();
			return static::evaluateElseClosures($arguments['__elseClosures'], $elseIfClosures, $renderingContext);
		}
		return '';
	}

	/**
	 * @param array $closures
	 * @param array $conditionClosures
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 */
	protected static function evaluateElseClosures(array $closures, array $conditionClosures, RenderingContextInterface $renderingContext) {
		foreach ($closures as $elseNodeIndex => $elseNodeClosure) {
			if (!isset($conditionClosures[$elseNodeIndex])) {
				return $elseNodeClosure();
			} else {
				if ($conditionClosures[$elseNodeIndex]()) {
					return $elseNodeClosure();
				}
			}
		}
		return '';
	}

	/**
	 * Returns value of "then" attribute.
	 * If then attribute is not set, iterates through child nodes and renders ThenViewHelper.
	 * If then attribute is not set and no ThenViewHelper and no ElseViewHelper is found, all child nodes are rendered
	 *
	 * @return mixed rendered ThenViewHelper or contents of <f:if> if no ThenViewHelper was found
	 * @api
	 */
	protected function renderThenChild() {
		if (isset($this->arguments['then'])) {
			return $this->arguments['then'];
		}
		if (isset($this->arguments['__thenClosure'])) {
			return $this->arguments['__thenClosure']();
		}

		$elseViewHelperEncountered = FALSE;
		foreach ((array) $this->childNodes as $childNode) {
			if ($childNode instanceof ViewHelperNode
				&& substr($childNode->getViewHelperClassName(), -14) === 'ThenViewHelper') {
				$data = $childNode->evaluate($this->renderingContext);
				return $data;
			}
			if ($childNode instanceof ViewHelperNode
				&& substr($childNode->getViewHelperClassName(), -14) === 'ElseViewHelper') {
				$elseViewHelperEncountered = TRUE;
			}
		}

		if ($elseViewHelperEncountered) {
			return '';
		} else {
			return $this->renderChildren();
		}
	}

	/**
	 * Returns value of "else" attribute.
	 * If else attribute is not set, iterates through child nodes and renders ElseViewHelper.
	 * If else attribute is not set and no ElseViewHelper is found, an empty string will be returned.
	 *
	 * @return string rendered ElseViewHelper or an empty string if no ThenViewHelper was found
	 * @api
	 */
	protected function renderElseChild() {
		if (isset($this->arguments['else'])) {
			return $this->arguments['else'];
		}
		if (isset($this->arguments['__elseClosures'])) {
			$elseIfClosures = isset($this->arguments['__elseifClosures']) ? $this->arguments['__elseifClosures'] : array();
			return static::evaluateElseClosures($this->arguments['__elseClosures'], $elseIfClosures, $this->renderingContext);
		}

		/** @var ViewHelperNode|NULL $elseNode */
		$elseNode = NULL;
		foreach ((array) $this->childNodes as $childNode) {
			if ($childNode instanceof ViewHelperNode
				&& substr($childNode->getViewHelperClassName(), -14) === 'ElseViewHelper') {
				$arguments = $childNode->getArguments();
				if (isset($arguments['if']) && $arguments['if']->evaluate($this->renderingContext)) {
					return $childNode->evaluate($this->renderingContext);
				}
			}
		}

		return '';
	}

	/**
	 * The compiled ViewHelper adds two new ViewHelper arguments: __thenClosure and __elseClosure.
	 * These contain closures which are be executed to render the then(), respectively else() case.
	 *
	 * @param string $argumentsName
	 * @param string $closureName
	 * @param string $initializationPhpCode
	 * @param ViewHelperNode $node
	 * @param TemplateCompiler $compiler
	 * @return string
	 */
	public function compile($argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler) {
		$thenViewHelperEncountered = $elseViewHelperEncountered = FALSE;
		foreach ($node->getChildNodes() as $childNode) {
			if ($childNode instanceof ViewHelperNode) {
				$viewHelperClassName = $childNode->getViewHelperClassName();
				if (substr($viewHelperClassName, -14) === 'ThenViewHelper') {
					$thenViewHelperEncountered = TRUE;
					$childNodesAsClosure = $compiler->wrapChildNodesInClosure($childNode);
					$initializationPhpCode .= sprintf('%s[\'__thenClosure\'] = %s;', $argumentsName, $childNodesAsClosure) . chr(10);
				} elseif (substr($viewHelperClassName, -14) === 'ElseViewHelper') {
					$elseViewHelperEncountered = TRUE;
					$childNodesAsClosure = $compiler->wrapChildNodesInClosure($childNode);
					$initializationPhpCode .= sprintf('%s[\'__elseClosures\'][] = %s;', $argumentsName, $childNodesAsClosure) . chr(10);
					$arguments = $childNode->getArguments();
					if (isset($arguments['if'])) {
						// The "else" has an argument, indicating it has a secondary (elseif) condition.
						// Compile a closure which will evaluate the condition.
						$elseIfConditionAsClosure = $compiler->wrapViewHelperNodeArgumentEvaluationInClosure($childNode, 'if');
						$initializationPhpCode .= sprintf('%s[\'__elseifClosures\'][] = %s;', $argumentsName, $elseIfConditionAsClosure) . chr(10);
					}
				}
			}
		}
		if (!$thenViewHelperEncountered && !$elseViewHelperEncountered && !isset($node->getArguments()['then'])) {
			$initializationPhpCode .= sprintf('%s[\'__thenClosure\'] = %s;', $argumentsName, $closureName) . chr(10);
		}
		return AbstractViewHelper::compile($argumentsName, $closureName, $initializationPhpCode, $node, $compiler);
	}
}
