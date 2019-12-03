<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Core\Exception;

/**
 * Class CompilableWithContentArgumentAndRenderStatic
 *
 * Provides default methods for rendering and compiling
 * any ViewHelper that conforms to the `renderStatic`
 * method pattern but has the added common use case that
 * an argument value must be checked and used instead of
 * the normal render children closure, if that named
 * argument is specified and not empty.
 *
 * Deprecated - should be avoided in favor of null-coalesce
 * of arguments and evaluateChildNodes, e.g.:
 *
 *     $content = $arguments['content'] ?? $this->evaluateChildNodes($renderingContext);
 *
 * Which serves the exact same purpose and avoids the
 * overhead of this trait.
 *
 * @deprecated Will be removed in Fluid 4.0
 */
trait CompileWithContentArgumentAndRenderStatic
{
    /**
     * @return string
     */
    protected function resolveContentArgumentName(): string
    {
        $registeredArguments = $this->getArguments()->getDefinitions();
        foreach ($registeredArguments as $registeredArgument) {
            if (!$registeredArgument->isRequired()) {
                $this->contentArgumentName = $registeredArgument->getName();
                return $this->contentArgumentName;
            }
        }
        throw new Exception(
            sprintf('Attempting to compile %s failed. Chosen compile method requires that ViewHelper has ' .
                'at least one registered and optional argument', __CLASS__)
        );
    }

    /**
     * Default render method to render ViewHelper with
     * first defined optional argument as content.
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
     * Helper which is mostly needed when calling renderStatic() from within
     * render().
     *
     * No public API yet.
     *
     * @return \Closure
     */
    protected function buildRenderChildrenClosure()
    {
        $argumentName = $this->resolveContentArgumentName();
        $arguments = $this->arguments ?? [];
        $self = $this;
        $renderChildrenClosure = function () use ($arguments, $argumentName, $self) {
            return !empty($argumentName) ? ($arguments[$argumentName] ?? $self->renderChildren()) : $self->renderChildren();
        };
        return $renderChildrenClosure;
    }

    abstract public function getArguments(): ArgumentCollection;
}
