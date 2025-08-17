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
 * EndsWith ViewHelper checks if the subject string ends with a specified string.
 * This ViewHelper implements an if/else condition.
 *
 * Examples
 * ========
 *
 * Render the body if "myString" ends with "World!"
 * -----------------------------------------------
 *
 * ::
 *
 *      <f:variable name="myString" value="Hello, World!" />
 *      <f:endsWith search="Hello" subject="{myString}">This will be rendered if variable "myString" ends with "World!"</f:endsWith>
 *
 * Output::
 *
 *      This will be rendered if variable "myString" ends with "World!"
 *
 * A more complex example with inline notation
 * -----------------------------------------
 *
 * ::
 *
 *      <f:variable name="condition" value="{false}" />
 *      <f:variable name="myString" value="Hello, World!" />
 *
 *      <f:if condition="{condition} || {f:endsWith(search: 'World!', subject: myString)}">
 *      It Works!
 *      </f:if>
 *
 * Output::
 *
 *      It Works!
 */
class EndsWithViewHelper extends AbstractConditionViewHelper
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
        return str_ends_with($arguments['subject'], $arguments['search']);
    }
}
