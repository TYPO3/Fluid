<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Variables\VariableExtractor;
use TYPO3Fluid\Fluid\Core\ViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Grouped loop view helper.
 * Loops through the specified values.
 *
 * The groupBy argument also supports property paths.
 *
 * = Examples =
 *
 * <code title="Simple">
 * <f:groupedFor each="{0: {name: 'apple', color: 'green'}, 1: {name: 'cherry', color: 'red'}, 2: {name: 'banana', color: 'yellow'}, 3: {name: 'strawberry', color: 'red'}}" as="fruitsOfThisColor" groupBy="color">
 *   <f:for each="{fruitsOfThisColor}" as="fruit">
 *     {fruit.name}
 *   </f:for>
 * </f:groupedFor>
 * </code>
 * <output>
 * apple cherry strawberry banana
 * </output>
 *
 * <code title="Two dimensional list">
 * <ul>
 *   <f:groupedFor each="{0: {name: 'apple', color: 'green'}, 1: {name: 'cherry', color: 'red'}, 2: {name: 'banana', color: 'yellow'}, 3: {name: 'strawberry', color: 'red'}}" as="fruitsOfThisColor" groupBy="color" groupKey="color">
 *     <li>
 *       {color} fruits:
 *       <ul>
 *         <f:for each="{fruitsOfThisColor}" as="fruit" key="label">
 *           <li>{label}: {fruit.name}</li>
 *         </f:for>
 *       </ul>
 *     </li>
 *   </f:groupedFor>
 * </ul>
 * </code>
 * <output>
 * <ul>
 *   <li>green fruits
 *     <ul>
 *       <li>0: apple</li>
 *     </ul>
 *   </li>
 *   <li>red fruits
 *     <ul>
 *       <li>1: cherry</li>
 *     </ul>
 *     <ul>
 *       <li>3: strawberry</li>
 *     </ul>
 *   </li>
 *   <li>yellow fruits
 *     <ul>
 *       <li>2: banana</li>
 *     </ul>
 *   </li>
 * </ul>
 * </output>
 *
 * Note: Using this view helper can be a sign of weak architecture. If you end up using it extensively
 * you might want to fine-tune your "view model" (the data you assign to the view).
 *
 * @api
 */
class GroupedForViewHelper extends AbstractViewHelper {

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
		$this->registerArgument('groupBy', 'string', 'Group by this property', TRUE);
		$this->registerArgument('groupKey', 'string', 'The name of the variable to store the current group', FALSE, 'groupKey');
	}

	/**
	 * Iterates through elements of $each and renders child nodes
	 *
	 * @return string Rendered string
	 * @throws ViewHelper\Exception
	 * @api
	 */
	public function render() {
		$each = $this->arguments['each'];
		$as = $this->arguments['as'];
		$groupBy = $this->arguments['groupBy'];
		$groupKey = $this->arguments['groupKey'];
		$output = '';
		if ($each === NULL) {
			return '';
		}
		if (is_object($each)) {
			if (!$each instanceof \Traversable) {
				throw new ViewHelper\Exception('GroupedForViewHelper only supports arrays and objects implementing \Traversable interface', 1253108907);
			}
			$each = iterator_to_array($each);
		}

		$groups = $this->groupElements($each, $groupBy);

		foreach ($groups['values'] as $currentGroupIndex => $group) {
			$this->templateVariableContainer->add($groupKey, $groups['keys'][$currentGroupIndex]);
			$this->templateVariableContainer->add($as, $group);
			$output .= $this->renderChildren();
			$this->templateVariableContainer->remove($groupKey);
			$this->templateVariableContainer->remove($as);
		}
		return $output;
	}

	/**
	 * Groups the given array by the specified groupBy property.
	 *
	 * @param array $elements The array / traversable object to be grouped
	 * @param string $groupBy Group by this property
	 * @return array The grouped array in the form array('keys' => array('key1' => [key1value], 'key2' => [key2value], ...), 'values' => array('key1' => array([key1value] => [element1]), ...), ...)
	 * @throws ViewHelper\Exception
	 */
	protected function groupElements(array $elements, $groupBy) {
		$extractor = new VariableExtractor();
		$groups = array('keys' => array(), 'values' => array());
		foreach ($elements as $key => $value) {
			if (is_array($value)) {
				$currentGroupIndex = isset($value[$groupBy]) ? $value[$groupBy] : NULL;
			} elseif (is_object($value)) {
				$currentGroupIndex = $extractor->getByPath($value, $groupBy);
			} else {
				throw new ViewHelper\Exception('GroupedForViewHelper only supports multi-dimensional arrays and objects', 1253120365);
			}
			$currentGroupKeyValue = $currentGroupIndex;
			if ($currentGroupIndex instanceof \DateTime) {
				$currentGroupIndex = $currentGroupIndex->format(\DateTime::RFC850);
			} elseif (is_object($currentGroupIndex)) {
				$currentGroupIndex = spl_object_hash($currentGroupIndex);
			}
			$groups['keys'][$currentGroupIndex] = $currentGroupKeyValue;
			$groups['values'][$currentGroupIndex][$key] = $value;
		}
		return $groups;
	}
}
