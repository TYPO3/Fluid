<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Variables\ScopedVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Declares a new variable scope which can be used to add locally scoped variables.
 *
 * Takes a "variables"-Parameter which is an associative array that defines the variables
 * that are initially available within this scope.
 *
 * These variables are only declared inside the ``<f:scope>...</f:scope>`` tag.
 *
 * After the closing tag, all initially and locally declared variables are removed respectively restored again.
 *
 * Examples
 * ========
 *
 * ::
 *     <f:variable name="foo" value="World" />
 *     <f:scope variables="{foo: 'Fluid', bar: 'Lorem'}">
 *         <f:variable name="baz" scope="local" value="Ipsum" />
 *         {bar} {baz} {foo}!
 *     </f:scope>
 *     Hello {foo}!
 *
 * Output::
 *
 *     Lorem Ipsum Fluid!
 *     Hello World!
 *
 * After the scope ``{foo}`` is restored, ``{bar}`` and ``{baz}`` is removed.
 *
 * @see \TYPO3Fluid\Fluid\ViewHelpers\VariableViewHelper
 * @api
 */
final class ScopeViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('variables', 'array', 'Array of variables that will be initially declared within this scope', false, []);
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        $globalVariableProvider = $this->renderingContext->getVariableProvider();
        $localVariableProvider = new StandardVariableProvider($this->arguments['variables']);
        $scopedVariableProvider = new ScopedVariableProvider($globalVariableProvider, $localVariableProvider);
        $this->renderingContext->setVariableProvider($scopedVariableProvider);
        $output = $this->renderChildren();
        $this->renderingContext->setVariableProvider($globalVariableProvider);
        return $output;
    }
}
