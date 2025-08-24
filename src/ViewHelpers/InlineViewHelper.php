<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Inline Fluid rendering ViewHelper
 *
 * Renders Fluid code stored in a variable, which you normally would
 * have to render before assigning it to the view. Instead you can
 * do the following (note, extremely simplified use case)::
 *
 *      $view->assign('variable', 'value of my variable');
 *      $view->assign('code', 'My variable: {variable}');
 *
 * And in the template::
 *
 *      {code -> f:inline()}
 *
 * Which outputs::
 *
 *      My variable: value of my variable
 *
 * You can use this to pass smaller and dynamic pieces of Fluid code
 * to templates, as an alternative to creating new partial templates.
 */
class InlineViewHelper extends AbstractViewHelper
{
    protected ?bool $escapeChildren = false;

    protected bool $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'code',
            'string',
            'Fluid code to be rendered as if it were part of the template rendering it. Can be passed as inline argument or tag content',
        );
    }

    public function render(): mixed
    {
        return $this->renderingContext->getTemplateParser()->parse((string)$this->renderChildren())->render($this->renderingContext);
    }

    /**
     * Explicitly set argument name to be used as content.
     */
    public function getContentArgumentName(): string
    {
        return 'code';
    }
}
