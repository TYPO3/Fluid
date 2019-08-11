<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Class CompilableWithRenderStatic
 *
 * Provides default methods for rendering and compiling
 * any ViewHelper that conforms to the `renderStatic`
 * method pattern.
 *
 * Deprecated - should not be used. No longer relevant.
 *
 * @deprecated Will be removed in Fluid 4.0
 */
trait CompileWithRenderStatic
{
    /**
     * Default render method - simply calls renderStatic() with a
     * prepared set of arguments.
     *
     * @return mixed Rendered result
     */
    public function render()
    {
        $arguments = $this->getArguments();
        return static::renderStatic(
            $arguments->getArrayCopy(),
            $this->buildRenderChildrenClosure(),
            $arguments->getRenderingContext()
        );
    }

    /**
     * @return \Closure
     */
    protected abstract function buildRenderChildrenClosure();
}
