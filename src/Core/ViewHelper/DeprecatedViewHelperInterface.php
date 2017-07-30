<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/**
 * Interface DeprecatedViewHelperInterface
 *
 * Contains methods which have become deprecated;
 * companion to trait DeprecatedViewHelper.
 */
interface DeprecatedViewHelperInterface
{
    /**
     * Initializes the view helper before invoking the render method.
     *
     * Override this method to solve tasks before the view helper content is rendered.
     *
     * @return void
     */
    public function initialize();

    /**
     * @param NodeInterface[] $nodes
     * @return void
     */
    public function setChildNodes(array $nodes);

    /**
     * Validate arguments, and throw exception if arguments do not validate.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateArguments();

}
