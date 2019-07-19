<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;

/**
 * This interface is implemented by objects which can be rendered
 * directly by Fluid.
 *
 * A "Renderable" is simply a named object which is aware of both
 * ViewHelperNode (during parse time only) and RenderingContext
 * during rendering wether template is compiled or not.
 */
interface RenderableInterface extends ComponentInterface
{
    /**
     * Returns the name of this Renderable - name must also be passed in constructor.
     * Implementations must always return a non-empty string even if setName() is not
     * called to set the specific name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Setter for the name of this Renderable.
     *
     * @param string $name
     * @return mixed
     */
    public function setName(string $name);

    /**
     * Sets the parsed RootNode which must be handled by this Renderable. In the
     * default implementation these nodes are evaluated by render() and extracted
     * by the NodeConverter
     *
     * @param ComponentInterface $node
     * @return RenderableInterface
     */
    public function setNode(ComponentInterface $node): RenderableInterface;

    /**
     * @return ComponentInterface
     */
    public function getNode(): ComponentInterface;

    /**
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public function render(RenderingContextInterface $renderingContext);
}
