<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

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
 * @deprecated Will be removed in v5. No longer necessary since getContentArgumentName() has been
 *             integrated into AbstractViewHelper with v2.15. Name has to be specified explicitly
 *             by overriding the method, implicit definition (= first optional argument) is no
 *             longer supported.
 */
trait CompileWithContentArgumentAndRenderStatic
{
    /**
     * Name of variable that contains the value to use
     * instead of render children closure, if specified.
     * If no name is provided here, the first variable
     * registered in `initializeArguments` of the ViewHelper
     * will be used.
     *
     * Note: it is significantly better practice defining
     * this property in your ViewHelper class and so fix it
     * to one particular argument instead of resolving,
     * especially when your ViewHelper is called multiple
     * times within an uncompiled template!
     *
     * This property cannot be directly set in consuming
     * ViewHelper, instead set the property in ViewHelper
     * constructor '__construct()', for example with
     * $this->contentArgumentName = 'explicitlyToUseArgumentName';
     *
     * Another possible way would be to override the method
     * 'resolveContentArgumentName()' and return the name.
     *
     * public function resolveContentArgumentName()
     * {
     *     return 'explicitlyToUseArgumentName';
     * }
     *
     * Note: Setting this through 'initializeArguments()' will
     *       not work as expected, and other methods should be
     *       avoided to override this.
     *
     * Following test ViewHelpers are tested and demonstrates
     * that the setting posibillities works.
     *
     * @var string
     */
    protected $contentArgumentName;

    /**
     * Default render method to render ViewHelper with
     * first defined optional argument as content.
     *
     * @return mixed Rendered result
     * @api
     */
    public function render()
    {
        trigger_error('CompileWithContentArgumentAndRenderStatic has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
        return static::renderStatic(
            $this->arguments,
            $this->buildRenderChildrenClosure(),
            $this->renderingContext,
        );
    }

    /**
     * @return string
     */
    public function resolveContentArgumentName()
    {
        trigger_error('CompileWithContentArgumentAndRenderStatic has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
        if (empty($this->contentArgumentName)) {
            $registeredArguments = $this->prepareArguments();
            foreach ($registeredArguments as $registeredArgument) {
                if (!$registeredArgument->isRequired()) {
                    $this->contentArgumentName = $registeredArgument->getName();
                    return $this->contentArgumentName;
                }
            }
            throw new Exception(
                sprintf('Attempting to compile %s failed. Chosen compile method requires that ViewHelper has ' .
                    'at least one registered and optional argument', __CLASS__),
            );
        }
        return $this->contentArgumentName;
    }

    public function getContentArgumentName(): ?string
    {
        return $this->resolveContentArgumentName();
    }
}
