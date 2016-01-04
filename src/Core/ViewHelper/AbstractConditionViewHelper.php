<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * This view helper is an abstract ViewHelper which implements an if/else condition.
 *
 * ### Usage
 *
 * This class provides all the basic logic to implement various kinds of condition
 * viewHelpers. To create a new condition viewHelper you need create a class that
 * extends from this class and either override the static ```evaluateCondition``` method
 * with the boolean logic or for more complex usecases override the ```renderStatic``` method.
 *
 * any conditionViewHelper can be used like the if viewHelper.
 *
 * #### inline usage
 *
 * ```html
 * {my:condition(..., then: 'condition is true', else: 'condition is false')}
 * ```
 *
 * #### block usage with then/else arguments
 *
 * ```html
 * <my:condition ... then="condition is true" else="condition is false" />
 * ```
 *
 * #### block usage with then/else tags
 *
 * ```html
 * <my:condition ... >
 * 	<f:then>condition is true</f:then>
 * 	<f:else>condition is false</f:else>
 * </my:condition>
 * ```
 *
 * #### block usage without then/else
 *
 * ```html
 * <my:condition ... >
 * 	condition is true
 * </my:condition>
 * ```
 *
 * > "..." in this example would be replaced by what arguments to evaluate a condition you
 * > add to the extending viewHelper class
 *
 *
 * @see TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper for a more detailed explanation.
 * Make sure to NOT OVERRIDE the constructor.
 */
abstract class AbstractConditionViewHelper extends AbstractViewHelper {

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
	 * Static method which can be overridden by subclasses. If a subclass
	 * requires a different (or faster) decision then this method is the one
	 * to override and implement.
	 *
	 * Note: method signature does not type-hint that an array is desired,
	 * and as such, *appears* to accept any input type. There is no type hint
	 * here for legacy reasons - the signature is kept compatible with third
	 * party packages which depending on PHP version would error out if this
	 * signature was not compatible with that of existing and in-production
	 * subclasses that will be using this base class in the future. Let this
	 * be a warning if someone considers changing this method signature!
	 *
	 * @param array|NULL $arguments
	 * @return boolean
	 * @api
	 */
	protected static function evaluateCondition($arguments = NULL) {
		return (boolean) $arguments['condition'];
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return mixed
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		if (static::evaluateCondition($arguments)) {
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
	private static function evaluateElseClosures(array $closures, array $conditionClosures, RenderingContextInterface $renderingContext) {
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
		if ($this->hasArgument('then')) {
			return $this->arguments['then'];
		}
		if ($this->hasArgument('__thenClosure')) {
			return $this->arguments['__thenClosure']();
		}

		$elseViewHelperEncountered = FALSE;
		foreach ($this->childNodes as $childNode) {
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

		if ($this->hasArgument('else')) {
			return $this->arguments['else'];
		}
		if ($this->hasArgument('__elseClosures')) {
			$elseIfClosures = isset($arguments['__elseifClosures']) ? $arguments['__elseifClosures'] : array();
			return static::evaluateElseClosures($arguments['__elseClosures'], $elseIfClosures, $this->renderingContext);
		}

		/** @var ViewHelperNode|NULL $elseNode */
		$elseNode = NULL;
		foreach ($this->childNodes as $childNode) {
			if ($childNode instanceof ViewHelperNode
				&& substr($childNode->getViewHelperClassName(), -14) === 'ElseViewHelper') {
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
		return parent::compile($argumentsName, $closureName, $initializationPhpCode, $node, $compiler);
	}
}
