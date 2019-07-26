<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

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
 */
trait CompileWithContentArgumentAndRenderStatic
{
    /**
     * @return string
     */
    protected function resolveContentArgumentName(): string
    {
        if (empty($this->contentArgumentName)) {
            #$registeredArguments = call_user_func_array([$this, 'getArguments'], []);
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
        return $this->contentArgumentName;
    }

    /**
     * Default render method to render ViewHelper with
     * first defined optional argument as content.
     *
     * @return mixed Rendered result
     * @api
     */
    public function render()
    {
        return static::renderStatic(
            $this->arguments->getArrayCopy(),
            $this->buildRenderChildrenClosure(),
            $this->arguments->getRenderingContext()
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
        if (!empty($argumentName) && isset($arguments[$argumentName])) {
            $renderChildrenClosure = function () use ($arguments, $argumentName) {
                return $arguments[$argumentName];
            };
        } else {
            $self = clone $this;
            $renderChildrenClosure = function () use ($self) {
                return $self->renderChildren();
            };
        }
        return $renderChildrenClosure;
    }
}
