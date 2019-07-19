<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Interface ViewHelperInterface
 *
 * Implemented by all ViewHelpers
 */
interface ViewHelperInterface extends ComponentInterface
{
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
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public function setRenderingContext(RenderingContextInterface $renderingContext);

    /**
     * Initialize the arguments of the ViewHelper, and call the render() method of the ViewHelper.
     *
     * @return mixed the rendered ViewHelper.
     */
    public function initializeArgumentsAndRender();

    /**
     * Validate arguments, and throw exception if arguments do not validate.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateArguments();

    /**
     * Method which can be implemented in any ViewHelper if that ViewHelper desires
     * the ability to allow additional, undeclared, dynamic etc. arguments for the
     * node in the template. Do not implement this unless you need it!
     *
     * @param array $arguments
     * @return void
     */
    public function handleAdditionalArguments(array $arguments);

    /**
     * Method which can be implemented in any ViewHelper if that ViewHelper desires
     * the ability to allow additional, undeclared, dynamic etc. arguments for the
     * node in the template. Do not implement this unless you need it!
     *
     * @param array $arguments
     * @return void
     */
    public function validateAdditionalArguments(array $arguments);

    /**
     * Called when being inside a cached template.
     *
     * @param \Closure $renderChildrenClosure
     * @return void
     */
    public function setRenderChildrenClosure(\Closure $renderChildrenClosure);

    /**
     * Returns whether the escaping interceptors should be disabled or enabled for the render-result of children of this ViewHelper
     *
     * Note: This method is no public API, use $this->escapeChildren instead!
     *
     * @return boolean
     */
    public function isChildrenEscapingEnabled(): bool;

    /**
     * Returns whether the escaping interceptors should be disabled or enabled for the render-result of this ViewHelper
     *
     * Note: This method is no public API, use $this->escapeOutput instead!
     *
     * @return boolean
     */
    public function isOutputEscapingEnabled(): bool;
}
