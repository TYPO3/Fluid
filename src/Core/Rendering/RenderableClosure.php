<?php
namespace TYPO3Fluid\Fluid\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Class RenderableClosure
 */
class RenderableClosure extends AbstractRenderable
{
    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * @param \Closure $closure
     * @return RenderableClosure
     */
    public function setClosure(\Closure $closure)
    {
        $this->closure = $closure;
        return $this;
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public function render(RenderingContextInterface $renderingContext)
    {
        return call_user_func_array($this->closure, [$renderingContext, $this->node]);
    }
}
