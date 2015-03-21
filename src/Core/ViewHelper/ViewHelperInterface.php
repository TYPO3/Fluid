<?php
namespace TYPO3\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Interface ViewHelperInterface
 *
 * Implemented by all ViewHelpers
 */
interface ViewHelperInterface {

	/**
	 * @return ArgumentDefinition[]
	 */
	public function prepareArguments();

	/**
	 * @param array $arguments
	 * @return void
	 */
	public function setArguments(array $arguments);

	/**
	 * @param NodeInterface[] $nodes
	 * @return void
	 */
	public function setChildNodes(array $nodes);

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @return void
	 */
	public function setRenderingContext(RenderingContextInterface $renderingContext);

	/**
	 * Initialize the arguments of the ViewHelper, and call the render() method of the ViewHelper.
	 *
	 * @return string the rendered ViewHelper.
	 */
	public function initializeArgumentsAndRender();

	/**
	 * Initializes the view helper before invoking the render method.
	 *
	 * Override this method to solve tasks before the view helper content is rendered.
	 *
	 * @return void
	 */
	public function initialize();

	/**
	 * Helper method which triggers the rendering of everything between the
	 * opening and the closing tag.
	 *
	 * @return mixed The finally rendered child nodes.
	 */
	public function renderChildren();

	/**
	 * Validate arguments, and throw exception if arguments do not validate.
	 *
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public function validateArguments();

	/**
	 * Initialize all arguments. You need to override this method and call
	 * $this->registerArgument(...) inside this method, to register all your arguments.
	 *
	 * @return void
	 */
	public function initializeArguments();

}
