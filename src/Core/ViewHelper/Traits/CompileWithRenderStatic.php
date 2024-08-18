<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

/**
 * Class CompilableWithRenderStatic
 *
 * Provides default methods for rendering and compiling
 * any ViewHelper that conforms to the `renderStatic`
 * method pattern.
 *
 * @deprecated Will be removed in v5. The non-static render() method
 *             should be used instead
 */
trait CompileWithRenderStatic
{
    /**
     * Default render method - simply calls renderStatic() with a
     * prepared set of arguments.
     *
     * @return mixed Rendered result
     * @api
     */
    public function render()
    {
        trigger_error('CompileWithRenderStatic has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
        return static::renderStatic(
            $this->arguments,
            $this->buildRenderChildrenClosure(),
            $this->renderingContext,
        );
    }

    /**
     * @return \Closure
     */
    abstract protected function buildRenderChildrenClosure();
}
