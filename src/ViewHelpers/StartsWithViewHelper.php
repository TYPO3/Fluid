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
 * A more complex example with inline notation
 * -----------------------------------------
 *
 * ::
 *
 *      <f:variable name="condition" value="{false}" />
 *      <f:variable name="myString" value="Hello, World!" />
 *
 *      <f:if condition="{condition} || {f:startsWith(search: 'Hello', subject: myString)}">
 *      It Works!
 *      </f:if>
 *
 * Output::
 *
 *      It Works!
 */
class StartsWithViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('subject', 'string', 'String to search in', true);
        $this->registerArgument('search', 'string', 'String to search in subject at the beginning', true);
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
