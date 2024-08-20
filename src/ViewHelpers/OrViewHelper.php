<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Or ViewHelper
 *
 * If content is null use alternative text.
 *
 * Usage of f:or
 * =============
 *
 * ::
 *
 *     {f:variable(name:'fallback',value:'this is not the variable you\'re looking for')}
 *     {undefinedVariable -> f:or(alternative:fallback)}
 *
 * Usage of ternary operator
 * =========================
 *
 * In some cases (e.g. when you want to check for empty instead of null)
 * it might be more handy to use a ternary operator instead of f:or
 *
 * ::
 *
 *     {emptyVariable ?: 'this is an alterative text'}
 */
class OrViewHelper extends AbstractViewHelper
{
    /**
     * Initialize
     */
    public function initializeArguments()
    {
        $this->registerArgument('content', 'mixed', 'Content to check if null');
        $this->registerArgument('alternative', 'mixed', 'Alternative if content is null');
        $this->registerArgument('arguments', 'array', 'Arguments to be replaced in the resulting string, using sprintf');
    }

    /**
     * @return mixed
     */
    public function render()
    {
        $alternative = $this->arguments['alternative'];
        $sprintfArguments = (array)$this->arguments['arguments'];
        if (empty($sprintfArguments)) {
            $sprintfArguments = null;
        }
        $content = $this->renderChildren();
        if (null === $content) {
            $content = $alternative;
        }
        if (false === empty($content)) {
            $content = null !== $sprintfArguments ? vsprintf($content, $sprintfArguments) : $content;
        }
        return $content;
    }

    /**
     * Explicitly set argument name to be used as content.
     */
    public function getContentArgumentName(): string
    {
        return 'content';
    }
}
