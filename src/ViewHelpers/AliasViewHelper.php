<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\ScopedVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Declares new variables which are aliases of other variables.
 * Takes a "map"-Parameter which is an associative array which defines the shorthand mapping.
 *
 * The variables are only declared inside the ``<f:alias>...</f:alias>`` tag. After the
 * closing tag, all declared variables are removed again.
 *
 * Using this ViewHelper can be a sign of weak architecture. If you end up
 * using it extensively you might want to fine-tune your "view model" (the
 * data you assign to the view).
 *
 * Examples
 * ========
 *
 * Single alias
 * ------------
 *
 * ::
 *
 *     <f:alias map="{x: 'foo'}">{x}</f:alias>
 *
 * Output::
 *
 *     foo
 *
 * Multiple mappings
 * -----------------
 *
 * ::
 *
 *     <f:alias map="{x: foo.bar.baz, y: foo.bar.baz.name}">
 *         {x.name} or {y}
 *     </f:alias>
 *
 * Output::
 *
 *     [name] or [name]
 *
 * Depending on ``{foo.bar.baz}``.
 *
 *
 * @api
 */
class AliasViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('map', 'array', 'Array that specifies which variables should be mapped to which alias', true);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $globalVariableProvider = $renderingContext->getVariableProvider();
        $localVariableProvider = new StandardVariableProvider($arguments['map']);
        $scopedVariableProvider = new ScopedVariableProvider($globalVariableProvider, $localVariableProvider);
        $renderingContext->setVariableProvider($scopedVariableProvider);

        $output = $renderChildrenClosure();

        $renderingContext->setVariableProvider($globalVariableProvider);

        return $output;
    }
}
