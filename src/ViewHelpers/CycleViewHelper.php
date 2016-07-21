<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This ViewHelper cycles through the specified values.
 * This can be often used to specify CSS classes for example.
 * **Note:** To achieve the "zebra class" effect in a loop you can also use the "iteration" argument of the **for** ViewHelper.
 *
 * = Examples =
 *
 * <code title="Simple">
 * <f:for each="{0:1, 1:2, 2:3, 3:4}" as="foo"><f:cycle values="{0: 'foo', 1: 'bar', 2: 'baz'}" as="cycle">{cycle}</f:cycle></f:for>
 * </code>
 * <output>
 * foobarbazfoo
 * </output>
 *
 * <code title="Alternating CSS class">
 * <ul>
 *   <f:for each="{0:1, 1:2, 2:3, 3:4}" as="foo">
 *     <f:cycle values="{0: 'odd', 1: 'even'}" as="zebraClass">
 *       <li class="{zebraClass}">{foo}</li>
 *     </f:cycle>
 *   </f:for>
 * </ul>
 * </code>
 * <output>
 * <ul>
 *   <li class="odd">1</li>
 *   <li class="even">2</li>
 *   <li class="odd">3</li>
 *   <li class="even">4</li>
 * </ul>
 * </output>
 *
 * Note: The above examples could also be achieved using the "iteration" argument of the ForViewHelper
 *
 * @api
 */
class CycleViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('values', 'array', 'The array or object implementing \ArrayAccess (for example \SplObjectStorage) to iterated over', FALSE);
		$this->registerArgument('as', 'strong', 'The name of the iteration variable', TRUE);
	}

	/**
	 * Renders cycle view helper
	 *
	 * @return string Rendered result
	 * @api
	 */
	public function render() {
		$values = $this->arguments['values'];
		$as = $this->arguments['as'];
		$scope = get_class($this);
		if ($values === NULL) {
			return $this->renderChildren();
		} elseif (is_object($values)) {
			if (!$values instanceof \Traversable) {
				throw new ViewHelper\Exception('CycleViewHelper only supports arrays and objects implementing \Traversable interface', 1248728393);
			}
			$values = iterator_to_array($values, FALSE);
		} else {
			$values = array_values($values);
		}

		if (!$this->viewHelperVariableContainer->exists($scope, $as)) {
			$currentIndex = 0;
		} else {
			$currentIndex = $this->viewHelperVariableContainer->get($scope, $as);
			if (!array_key_exists($currentIndex, $values)) {
				$currentIndex = 0;
			}
		}

		$currentValue = isset($values[$currentIndex]) ? $values[$currentIndex] : NULL;
		$this->templateVariableContainer->add($as, $currentValue);
		$output = $this->renderChildren();
		$this->templateVariableContainer->remove($as);

		$this->viewHelperVariableContainer->addOrUpdate($scope, $as, $currentIndex + 1);

		return $output;
	}

}
