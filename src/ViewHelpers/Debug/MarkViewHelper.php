<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Debug;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Profiling MarkViewHelper
 *
 * Use any number of times with the same name to cause
 * execution timing marks to be generated. Using the same
 * name as used in a (parent or just preceeding) f:profile
 * ViewHelper causes the time marks to be added to that.
 *
 * Like the root f:profile ViewHelper this VieWHelper works
 * both in compiled and uncompilable templates and can be
 * used inline without disrupting rendering:
 *
 * ```xml
 * <f:for each="{manyItems -> f:debug.mark(name: 'manyItemsLoop')}" as="item">
 *     <!-- render an item -->
 *     <f:debug.mark name="manyItemsLoop" />
 * <f:for>
 * ```
 *
 * If `{manyItems}` then contains 5 items, exactly 6
 * marks will be recorded: the first one at the exact
 * time when the iterated variable has been read into
 * memory, and one mark for each of the 5 iterations,
 * at the exact same point in the loop. Example array:
 *
 * ```php
 * $marksForManyItemsLoop = array(
 *     1444617162.852,
 *     0.431154,
 *     0.42415513,
 *     0.43169951,
 *     0.3971183
 *     0.400135
 * );
 * ```
 *
 * Which basically means that the mark sequence started
 * at the `microtime(TRUE)` of the very first mark that
 * was recorded, and that each mark after that adds X.Y
 * seconds to the rendering. The values illustrated here
 * are *quite* high (near half a second per loop) so you
 * may experience much, much lower numbers.
 *
 * Note that the composition of this array allows you to
 * measure with PHP math functions the total time of all
 * marks and the average time of each mark:
 *
 * ```php
 * $marksOnly = array_slice($marksForManyItems, 1);
 * $total = array_sum(array_slice($marksForManyItems, 1));
 * $average = $total / (count($marksForManyItems) - 1);
 * ```
 *
 * Finally: this ViewHelper is only intended to be used
 * in development or testing context *but* can be left in
 * place safely also in production as long as `f:profile`
 * is not used simultaneously (in the same template). When
 * used this way the ViewHelper still records marks but
 * can only report them if you manually use the getMarks()
 * function in your code that executes after the template
 * has been rendered.
 *
 * @api
 */
class MarkViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeChildren = FALSE;

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @var array
	 */
	protected static $marks = array();

	/**
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of this profiling run - should be unique', TRUE);
	}

	/**
	 * @return string
	 */
	public function render() {
		return static::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return mixed
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		$name = $arguments['name'];
		$start = 0;
		if (!array_key_exists($name, static::$marks)) {
			static::$marks[$name] = array();
		} else {
			$start = static::$marks[$name][0];
		}
		static::$marks[$arguments['name']][] = microtime(TRUE) - $start;
		return parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
	}

}
