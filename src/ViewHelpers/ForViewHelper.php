<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Loop view helper which can be used to iterate over arrays.
 * Implements what a basic foreach()-PHP-method does.
 *
 * = Examples =
 *
 * <code title="Simple Loop">
 * <f:for each="{0:1, 1:2, 2:3, 3:4}" as="foo">{foo}</f:for>
 * </code>
 * <output>
 * 1234
 * </output>
 *
 * <code title="Output array key">
 * <ul>
 *   <f:for each="{fruit1: 'apple', fruit2: 'pear', fruit3: 'banana', fruit4: 'cherry'}" as="fruit" key="label">
 *     <li>{label}: {fruit}</li>
 *   </f:for>
 * </ul>
 * </code>
 * <output>
 * <ul>
 *   <li>fruit1: apple</li>
 *   <li>fruit2: pear</li>
 *   <li>fruit3: banana</li>
 *   <li>fruit4: cherry</li>
 * </ul>
 * </output>
 *
 * <code title="Iteration information">
 * <ul>
 *   <f:for each="{0:1, 1:2, 2:3, 3:4}" as="foo" iteration="fooIterator">
 *     <li>Index: {fooIterator.index} Cycle: {fooIterator.cycle} Total: {fooIterator.total}{f:if(condition: fooIterator.isEven, then: ' Even')}{f:if(condition: fooIterator.isOdd, then: ' Odd')}{f:if(condition: fooIterator.isFirst, then: ' First')}{f:if(condition: fooIterator.isLast, then: ' Last')}</li>
 *   </f:for>
 * </ul>
 * </code>
 * <output>
 * <ul>
 *   <li>Index: 0 Cycle: 1 Total: 4 Odd First</li>
 *   <li>Index: 1 Cycle: 2 Total: 4 Even</li>
 *   <li>Index: 2 Cycle: 3 Total: 4 Odd</li>
 *   <li>Index: 3 Cycle: 4 Total: 4 Even Last</li>
 * </ul>
 * </output>
 *
 * @api
 */
class ForViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('each', 'array', 'The array or \SplObjectStorage to iterated over', TRUE);
		$this->registerArgument('as', 'string', 'The name of the iteration variable', TRUE);
		$this->registerArgument('key', 'string', 'Variable to assign array key to', FALSE);
		$this->registerArgument('reverse', 'boolean', 'If TRUE, iterates in reverse', FALSE, FALSE);
		$this->registerArgument('iteration', 'string', 'The name of the variable to store iteration information (index, cycle, isFirst, isLast, isEven, isOdd)', FALSE, NULL);
	}

	/**
	 * Iterates through elements of $each and renders child nodes
	 *
	 * @return string Rendered string
	 * @api
	 */
	public function render() {
		return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
	}

	/**
	 * @param string $argumentsName
	 * @param string $closureName
	 * @param string $initializationPhpCode
	 * @param ViewHelperNode $node
	 * @param TemplateCompiler $compiler
	 * @return string
	 */
	public function compile($argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler) {
		return sprintf('%s::renderStatic(%s, %s, $renderingContext)', get_class($this), $argumentsName, $closureName);
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 * @throws ViewHelper\Exception
	 */
	public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		$templateVariableContainer = $renderingContext->getVariableProvider();
		if ($arguments['each'] === NULL) {
			return '';
		}
		if (is_object($arguments['each']) && !$arguments['each'] instanceof \Traversable) {
			throw new ViewHelper\Exception('ForViewHelper only supports arrays and objects implementing \Traversable interface', 1248728393);
		}

		if ($arguments['reverse'] === TRUE) {
			// array_reverse only supports arrays
			if (is_object($arguments['each'])) {
				/** @var $each \Traversable */
				$each = $arguments['each'];
				$arguments['each'] = iterator_to_array($each);
			}
			$arguments['each'] = array_reverse($arguments['each']);
		}
		$iterationData = array(
			'index' => 0,
			'cycle' => 1,
			'total' => count($arguments['each'])
		);

		$output = '';
		foreach ($arguments['each'] as $keyValue => $singleElement) {
			$templateVariableContainer->add($arguments['as'], $singleElement);
			if ($arguments['key'] !== NULL) {
				$templateVariableContainer->add($arguments['key'], $keyValue);
			}
			if ($arguments['iteration'] !== NULL) {
				$iterationData['isFirst'] = $iterationData['cycle'] === 1;
				$iterationData['isLast'] = $iterationData['cycle'] === $iterationData['total'];
				$iterationData['isEven'] = $iterationData['cycle'] % 2 === 0;
				$iterationData['isOdd'] = !$iterationData['isEven'];
				$templateVariableContainer->add($arguments['iteration'], $iterationData);
				$iterationData['index']++;
				$iterationData['cycle']++;
			}
			$output .= $renderChildrenClosure();
			$templateVariableContainer->remove($arguments['as']);
			if ($arguments['key'] !== NULL) {
				$templateVariableContainer->remove($arguments['key']);
			}
			if ($arguments['iteration'] !== NULL) {
				$templateVariableContainer->remove($arguments['iteration']);
			}
		}
		return $output;
	}
}
