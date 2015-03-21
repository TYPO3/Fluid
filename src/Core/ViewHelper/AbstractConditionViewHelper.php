<?php
namespace TYPO3\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

/**
 * This view helper is an abstract ViewHelper which implements an if/else condition.
 * @see TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode::convertArgumentValue() to find see how boolean arguments are evaluated
 *
 * = Usage =
 *
 * To create a custom Condition ViewHelper, you need to subclass this class, and
 * implement your own render() method. Inside there, you should call $this->renderThenChild()
 * if the condition evaluated to TRUE, and $this->renderElseChild() if the condition evaluated
 * to FALSE.
 *
 * Every Condition ViewHelper has a "then" and "else" argument, so it can be used like:
 * <[aConditionViewHelperName] .... then="condition true" else="condition false" />,
 * or as well use the "then" and "else" child nodes.
 *
 * @see TYPO3\Fluid\ViewHelpers\IfViewHelper for a more detailed explanation and a simple usage example.
 * Make sure to NOT OVERRIDE the constructor.
 *
 * @api
 */
abstract class AbstractConditionViewHelper extends AbstractViewHelper implements CompilableInterface {

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * Initializes the "then" and "else" arguments
	 */
	public function initializeArguments() {
		$this->registerArgument('then', 'mixed', 'Value to be returned if the condition if met.', FALSE);
		$this->registerArgument('else', 'mixed', 'Value to be returned if the condition if not met.', FALSE);
		$this->registerArgument('condition', 'boolean', 'Condition expression conforming to Fluid boolean rules', FALSE, FALSE);
	}

	/**
	 * Renders <f:then> child if $condition is true, otherwise renders <f:else> child.
	 *
	 * @param boolean $condition View helper condition
	 * @return string the rendered string
	 * @api
	 */
	public function render() {
		if ($this->arguments['condition']) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
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
		if ($this->hasArgument('then')) {
			return $this->arguments['then'];
		}
		if ($this->hasArgument('__thenClosure')) {
			$thenClosure = $this->arguments['__thenClosure'];
			return $thenClosure();
		} elseif ($this->hasArgument('__elseClosure')) {
			return '';
		}

		$elseViewHelperEncountered = FALSE;
		foreach ($this->childNodes as $childNode) {
			if ($childNode instanceof ViewHelperNode
				&& $childNode->getViewHelperClassName() === 'TYPO3\Fluid\ViewHelpers\ThenViewHelper') {
				$data = $childNode->evaluate($this->renderingContext);
				return $data;
			}
			if ($childNode instanceof ViewHelperNode
				&& $childNode->getViewHelperClassName() === 'TYPO3\Fluid\ViewHelpers\ElseViewHelper') {
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

		if ($this->hasArgument('else')) {
			return $this->arguments['else'];
		}
		if ($this->hasArgument('__elseifClosures') && $this->hasArgument('__elseClosures')) {
			foreach ($this->arguments['__elseifClosures'] as $elseifNodeIndex => $elseifNodeClosure) {
				if ($elseifNodeClosure($this->renderingContext, $this)) {
					return $this->arguments['__elseClosures'][$elseifNodeIndex]();
				}
			}
		}
		if ($this->hasArgument('__elseClosure')) {
			$elseClosure = $this->arguments['__elseClosure'];
			return $elseClosure();
		}

		/** @var ViewHelperNode|NULL $elseNode */
		$elseNode = NULL;
		foreach ($this->childNodes as $childNode) {
			if ($childNode instanceof ViewHelperNode
				&& $childNode->getViewHelperClassName() === 'TYPO3\Fluid\ViewHelpers\ElseViewHelper') {
				$arguments = $childNode->getArguments();
				if (isset($arguments['if']) && $arguments['if']->evaluate($this->renderingContext)) {
					return $childNode->evaluate($this->renderingContext);
				} else {
					$elseNode = $childNode;
				}
			}
		}

		return $elseNode instanceof ViewHelperNode ? $elseNode->evaluate($this->renderingContext) : '';
	}

	/**
	 * The compiled ViewHelper adds two new ViewHelper arguments: __thenClosure and __elseClosure.
	 * These contain closures which are be executed to render the then(), respectively else() case.
	 *
	 * @param string $argumentsName
	 * @param string $closureName
	 * @param string $initializationPhpCode
	 * @param NodeInterface $node
	 * @param TemplateCompiler $compiler
	 * @return string
	 */
	public function compile($argumentsName, $closureName, &$initializationPhpCode, NodeInterface $node, TemplateCompiler $compiler) {
		foreach ($node->getChildNodes() as $childNode) {
			if ($childNode instanceof ViewHelperNode && $childNode->getViewHelperClassName() === 'TYPO3\Fluid\ViewHelpers\ThenViewHelper') {
				$childNodesAsClosure = $compiler->wrapChildNodesInClosure($childNode);
				$initializationPhpCode .= sprintf('%s[\'__thenClosure\'] = %s;', $argumentsName, $childNodesAsClosure) . chr(10);
			}
			if ($childNode instanceof ViewHelperNode && $childNode->getViewHelperClassName() === 'TYPO3\Fluid\ViewHelpers\ElseViewHelper') {
				$childNodesAsClosure = $compiler->wrapChildNodesInClosure($childNode);
				if ($childNode->getArguments()) {
					$nodeEvaluationAsClosure = $compiler->wrapViewHelperNodeArgumentEvaluationInClosure($childNode, 'if');
					$initializationPhpCode .= sprintf('%s[\'__elseClosures\'][] = %s;', $argumentsName, $childNodesAsClosure) . chr(10);
					$initializationPhpCode .= sprintf('%s[\'__elseifClosures\'][] = %s;', $argumentsName, $nodeEvaluationAsClosure) . chr(10);
				} else {
					$initializationPhpCode .= sprintf('%s[\'__elseClosure\'] = %s;', $argumentsName, $childNodesAsClosure) . chr(10);
				}
			}
		}
		return TemplateCompiler::SHOULD_GENERATE_VIEWHELPER_INVOCATION;
	}
}
