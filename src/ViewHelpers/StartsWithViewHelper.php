<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * StartsWith ViewHelper checks if the subject string starts with a specified string.
 * This ViewHelper implements an if/else condition.
 *
 * Examples
 * ========
 *
 * Render the body if "myString" starts with "Hello"
 * -----------------------------------------------
 *
 * ::
 *
 *      <f:variable name="myString" value="Hello, World!" />
 *      <f:startsWith search="Hello" subject="{myString}">This will be rendered if variable "myString" starts with "Hello"</f:startsWith>
 *
 * Output::
 *
 *      This will be rendered if variable "myString" starts with "Hello"
 *
 * Inline notation
 * --------------
 *
 * ::
 *
 *      {f:variable(name: 'mystring', value: 'Hello, World!')}
 *      {mystring -> f:startsWith(search: "Hello")}
 *
 * Output::
 *
 *      Hello, World!
 */
class StartsWithViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('subject', 'string', 'String to search in');
        $this->registerArgument('search', 'string', 'String to search in subject at the beginning', true);
    }

    public function render()
    {
        $arguments = $this->arguments;
        $arguments['subject'] = $this->arguments['subject'] ?? $this->renderThenChild();
        $this->setArguments($arguments);

        return parent::render();
    }

    /**
     * @param array $arguments
     * @param RenderingContextInterface $renderingContext
     * @return bool
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        return str_starts_with($arguments['subject'], $arguments['search']);
    }
}
