<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as ViewHelperException;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Declares new variables which are aliases of other variables.
 * Takes a "map"-Parameter which is an associative array which defines the shorthand mapping.
 *
 * The variables are only declared inside the ``<f:alias>...</f:alias>``-tag. After the
 * closing tag, all declared variables are removed again.
 *
 * External data like JSON can be consumed using the "src" and "as" arguments, optionally
 * specifying the "type" manually. Both local and external (HTTP or other stream wrapper)
 * accessible JSON sources can be used - "src" supports local files and URIs alike.
 *
 * = Examples =
 *
 * <code title="Single alias">
 * <f:alias map="{x: 'foo'}">{x}</f:alias>
 * </code>
 * <output>
 * foo
 * </output>
 *
 * <code title="Multiple mappings">
 * <f:alias map="{x: foo.bar.baz, y: foo.bar.baz.name}">
 *   {x.name} or {y}
 * </f:alias>
 * </code>
 * <output>
 * [name] or [name]
 * depending on {foo.bar.baz}
 * </output>
 *
 * <code title="VariableProvider with JSON">
 * <f:alias src="/path/to/file.json" as="json">
 *   {json.name} at {json.phone}
 * </f:alias>
 * <output>
 *   // If "file.json" contains {"name": "John", "phone": "1-800-FLUID"}:
 *   John at 1-800-FLUID
 * </code>
 *
 * Note: Using this view helper can be a sign of weak architecture. If you end up using it extensively
 * you might want to fine-tune your "view model" (the data you assign to the view).
 *
 * @api
 */
class AliasViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @var array
	 */
	protected static $sources = array();

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('map', 'array', 'Array that specifies which variables should be mapped to which alias');
		$this->registerArgument('src', 'mixed', 'Source (file, URL, object, array - depends on "type") containing variables to insert. When used must be combined with "name" argument.');
		$this->registerArgument('as', 'string', 'Name of template variable which will contain variables read from "src". Required when "src" is used.');
		$this->registerArgument('type', 'string', 'Type of "src", currently only "json" is supported. Can be specified if the type cannot be detected based on file name of "src".');
	}

	/**
	 * @thrpws ParserException
	 */
	public function validateArguments() {
		parent::validateArguments();
		if ((empty($this->arguments['map']) && empty($this->arguments['src'])) || (!empty($this->arguments['map']) && !empty($this->arguments['src']))) {
			throw new ParserException('Either "map" or "src" argument must be specified (not both)');
		} elseif (!empty($this->arguments['src']) && empty($this->arguments['as'])) {
			throw new ParserException('Argument "src" must be used together with "as" argument');
		} elseif (!empty($this->arguments['map']) && !empty($this->arguments['as'])) {
			throw new ParserException('Argument "as" cannot be used together with argument "map" (works with "src" only)');
		}
	}

	/**
	 * Renders alias
	 *
	 * @return string Rendered string
	 * @api
	 */
	public function render() {
		return static::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
	}

	/**
	 * @param array $map
	 * @param callable $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 */
	protected static function renderUsingMap(array $map, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		$provider = $renderingContext->getVariableProvider();
		foreach ($map as $aliasName => $value) {
			$provider->add($aliasName, $value);
		}
		$output = $renderChildrenClosure();
		foreach ($map as $aliasName => $value) {
			$provider->remove($aliasName);
		}
		return $output;
	}

	/**
	 * @param string $source
	 * @param string|NULL $type
	 * @return VariableProviderInterface
	 */
	protected static function createVariableProviderFromSource($source, $type) {
		if (empty($type)) {
			$type = strtolower(pathinfo($source, PATHINFO_EXTENSION));
			if (empty($type)) {
				throw new ViewHelperException('The type of source could not be detected - please provide it using the "type" argument');
			}
		}
		$expectedVariableProviderClassName = sprintf('TYPO3Fluid\\Fluid\\Core\\Variables\\%sVariableProvider', ucfirst($type));
		if (!class_exists($expectedVariableProviderClassName)) {
			throw new ViewHelperException(sprintf('Induced variable provider class name "%s" does not exist', $expectedVariableProviderClassName));
		}
		if (!isset(static::$sources[$source])) {
			/** @var VariableProviderInterface $provider */
			static::$sources[$source] = new $expectedVariableProviderClassName();
			static::$sources[$source]->setSource($source);
		}
		return static::$sources[$source];
	}

	/**
	 * @param array $arguments
	 * @param callable $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		$map = array();
		if (!empty($arguments['map'])) {
			$map = $arguments['map'];
		} elseif (!empty($arguments['src'])) {
			$map = array($arguments['as'] => static::createVariableProviderFromSource($arguments['src'], $arguments['type'])->getAll());
		}
		return static::renderUsingMap($map, $renderChildrenClosure, $renderingContext);
	}

}
