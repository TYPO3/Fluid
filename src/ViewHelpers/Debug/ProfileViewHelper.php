<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Debug;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;

/**
 * ViewHelper which profiles the Fluid template code
 * nested within the tag. Collects metrics about the
 * rendering of nodes, number of nodes used etc.
 *
 * ```xml
 * <f:debug.profile name="somethingExpensive">
 *     <!-- as much and any type of template code you want -->
 * </f:debug.profile>
 * ```
 *
 * You can put the tag anywhere in the nesting depth
 * around any amount of template code - you can target
 * exactly the little chunk of code you suspect is slow.
 *
 * Works both in compilable and uncompilable templates,
 * but only collects node metrics when when template is
 * uncompilable or during compiling; once compiled the
 * ViewHelper only reports basic metrics about the
 * rendering of the child nodes closure.
 *
 * Contrary to other ViewHelpers this ViewHelper does
 * not output the profiling results immediately. Rather,
 * it collects all profiles and outputs them together
 * when the template code is finished rendering. The
 * tag content gets returned transparently which means
 * you can chain this ViewHelper to profile expensive
 * calls to inline ViewHelpers too:
 *
 * ```xml
 * <f:for each="{my:expensive() -> f:debug.profile(name: 'expensive')}" as="item">
 *     <!-- the "item" variable is the same as if f:profile was not used -->
 * </f:for>
 * ```
 *
 * Note: This view helper is only meant to be used during
 * development or testing.
 *
 * @api
 */
class ProfileViewHelper extends AbstractViewHelper {

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
	protected static $profiles = array();

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
	 * @param NodeInterface[] $childNodes
	 * @return void
	 */
	public function setChildNodes(array $childNodes) {
		parent::setChildNodes($childNodes);
		$name = $this->arguments['name'];
		if (!array_key_exists($name, static::$profiles)) {
			static::$profiles[$name] = array();
		}
		static::$profiles[$name]['depth'] = 1;
		static::$profiles[$name]['nodes'] = $this->countNodesRecursive($childNodes, static::$profiles[$name]['depth']);
	}

	/**
	 * @param NodeInterface[] $childNodes
	 * @param integer $depth
	 * @return integer
	 */
	protected function countNodesRecursive(array $childNodes, &$depth) {
		$nodes = count($childNodes);
		foreach ($childNodes as $childNode) {
			$currentDepth = $depth;
			$nodes += $this->countNodesRecursive($childNode->getChildNodes(), $currentDepth);
			$depth = max($currentDepth, $depth);
		}
		++ $depth;
		return $nodes;
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return mixed
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		$name = $arguments['name'];
		if (!array_key_exists($name, static::$profiles)) {
			static::$profiles[$name] = array();
		}
		$time = microtime(TRUE);
		$memory = memory_get_usage(TRUE);
		$content = $renderChildrenClosure();
		$profile = array(
			'time' => microtime(TRUE) - $time,
			'memory' => memory_get_usage(TRUE) - $memory
		);
		if (is_scalar($content)) {
			// Only report size of variables that have a byte compatible size
			$profile['size'] = strlen((string) $content);
		}

		return $content;
	}

	/**
	 * @return array
	 */
	static public function getProfiles() {
		return static::$profiles;
	}

}
