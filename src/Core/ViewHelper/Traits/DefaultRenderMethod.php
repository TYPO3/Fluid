<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class DefaultRenderMethod
 *
 * Contains a default implementation of a render method which calls
 * renderStatic.
 *
 * Implement this trait to indicate that your ViewHelper is exclusively
 * static callable and implements renderStatic().
 */
trait DefaultRenderMethod
{
    /**
     * Forced implementation to build a rendering closure
     *
     * @return \Closure
     */
    abstract public function buildRenderChildrenClosure();

    /**
     * @return mixed
     */
    abstract static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    );

    /**
     * Default render method to render ViewHelper with
     * first defined optional argument as content.
     *
     * @return string Rendered string
     * @api
     */
    public function render()
    {
        return static::renderStatic(
            $this->arguments,
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }
}
